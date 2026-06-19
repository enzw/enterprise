<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'sales_manager', 'admin');

        $invoices = SalesInvoice::with('customer', 'salesOrder')
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(20);

        return view('sales-invoices.index', compact('invoices'));
    }

    public function create(SalesOrder $salesOrder)
    {
        $this->checkRole('ar_analyst', 'admin');

        $salesOrder->load('customer', 'location', 'items.item');

        if (! $salesOrder->items->contains(fn ($line) => $line->quantity_shipped > $line->quantity_invoiced)) {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', 'There are no shipped and uninvoiced quantities');
        }

        return view('sales-invoices.create', compact('salesOrder'));
    }

    public function store(Request $request, SalesOrder $salesOrder)
    {
        $this->checkRole('ar_analyst', 'admin');

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.so_item_id' => 'required|distinct|exists:sales_order_items,id',
            'items.*.quantity' => 'nullable|integer|min:0',
        ]);

        $invoice = DB::transaction(function () use ($salesOrder, $validated) {
            $salesOrder = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);
            $submittedLines = collect($validated['items'])
                ->filter(fn (array $line) => (int) ($line['quantity'] ?? 0) > 0);

            if ($submittedLines->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a quantity for at least one invoice line.',
                ]);
            }

            $preparedLines = [];
            $subtotal = 0;

            foreach ($submittedLines as $submittedLine) {
                $soItem = SalesOrderItem::with('item')
                    ->where('sales_order_id', $salesOrder->id)
                    ->lockForUpdate()
                    ->find($submittedLine['so_item_id']);

                if (! $soItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected lines does not belong to this Sales Order.',
                    ]);
                }

                $quantity = (int) $submittedLine['quantity'];
                $available = $soItem->quantity_shipped - $soItem->quantity_invoiced;

                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        'items' => "Invoice quantity for {$soItem->item->name} exceeds the shipped and uninvoiced quantity ({$available}).",
                    ]);
                }

                $lineAmount = round($quantity * (float) $soItem->unit_price, 2);
                $subtotal += $lineAmount;
                $preparedLines[] = [
                    'sales_order_item_id' => $soItem->id,
                    'item_id' => $soItem->item_id,
                    'description' => "{$soItem->item->sku} - {$soItem->item->name}",
                    'quantity' => $quantity,
                    'unit_price' => $soItem->unit_price,
                    'line_amount' => $lineAmount,
                ];
            }

            $invoice = SalesInvoice::create([
                'subsidiary_id' => $salesOrder->subsidiary_id,
                'customer_id' => $salesOrder->customer_id,
                'sales_order_id' => $salesOrder->id,
                'created_by' => auth()->id(),
                'invoice_number' => $this->nextInvoiceNumber(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'draft',
                'currency_code' => $salesOrder->currency_code,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'memo' => $validated['memo'] ?? null,
            ]);

            foreach ($preparedLines as $line) {
                $line['sales_invoice_id'] = $invoice->id;
                SalesInvoiceItem::create($line);
                SalesOrderItem::whereKey($line['sales_order_item_id'])
                    ->increment('quantity_invoiced', $line['quantity']);
            }

            $this->refreshSalesOrderStatus($salesOrder);

            return $invoice;
        });

        return redirect()
            ->route('sales-invoices.show', $invoice)
            ->with('success', 'Sales Invoice created in draft status');
    }

    public function show(SalesInvoice $invoice)
    {
        $this->checkRole('ar_analyst', 'accounting_manager', 'sales_manager', 'admin');

        $invoice->load([
            'customer',
            'subsidiary',
            'salesOrder',
            'createdBy',
            'items.item.accounts',
            'payments.payment',
        ]);

        return view('sales-invoices.show', compact('invoice'));
    }

    public function submit(SalesInvoice $invoice)
    {
        $this->checkRole('ar_analyst', 'admin');

        if ($invoice->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft invoices can be submitted for approval');
        }

        $invoice->update(['status' => 'pending_approval']);

        return redirect()->back()->with('success', 'Sales Invoice submitted to the Accounting Manager');
    }

    public function approve(SalesInvoice $invoice)
    {
        $this->checkRole('accounting_manager', 'admin');

        DB::transaction(function () use ($invoice) {
            $invoice = SalesInvoice::with('customer', 'items.item.accounts')
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            if ($invoice->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'status' => 'Only invoices pending approval can be approved.',
                ]);
            }

            $availableCredit = (float) $invoice->customer->credit_limit - (float) $invoice->customer->credit_used;
            if ((float) $invoice->total > $availableCredit) {
                throw ValidationException::withMessages([
                    'credit' => "Customer credit is insufficient. Available credit: {$availableCredit}.",
                ]);
            }

            $arAccount = Account::query()
                ->where('subsidiary_id', $invoice->subsidiary_id)
                ->where('number', '1110')
                ->lockForUpdate()
                ->first();

            if (! $arAccount) {
                throw ValidationException::withMessages([
                    'account' => 'Accounts Receivable account 1110 was not found.',
                ]);
            }

            foreach ($invoice->items as $line) {
                $incomeAccountId = $line->item?->accounts?->income_account_id;
                if (! $incomeAccountId) {
                    throw ValidationException::withMessages([
                        'account' => "Income account is not configured for {$line->description}.",
                    ]);
                }

                Account::whereKey($incomeAccountId)->increment('balance', $line->line_amount);
            }

            $arAccount->increment('balance', $invoice->total);
            $invoice->customer->increment('credit_used', $invoice->total);
            $invoice->update(['status' => 'approved']);
        });

        return redirect()->back()->with('success', 'Sales Invoice approved and posted to Accounts Receivable');
    }

    public function reject(SalesInvoice $invoice)
    {
        $this->checkRole('accounting_manager', 'admin');

        if ($invoice->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only invoices pending approval can be rejected');
        }

        $invoice->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Sales Invoice returned to draft for review and resubmission');
    }

    public function cancel(SalesInvoice $invoice)
    {
        $this->checkRole('ar_analyst', 'admin');

        DB::transaction(function () use ($invoice) {
            $invoice = SalesInvoice::with('items')
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft invoices can be cancelled.',
                ]);
            }

            foreach ($invoice->items as $line) {
                SalesOrderItem::whereKey($line->sales_order_item_id)
                    ->decrement('quantity_invoiced', $line->quantity);
            }

            $invoice->update(['status' => 'cancelled']);

            if ($invoice->sales_order_id) {
                $this->refreshSalesOrderStatus(SalesOrder::findOrFail($invoice->sales_order_id));
            }
        });

        return redirect()->back()->with('success', 'Draft invoice cancelled and quantities released');
    }

    private function refreshSalesOrderStatus(SalesOrder $salesOrder): void
    {
        $allInvoiced = ! $salesOrder->items()
            ->whereColumn('quantity_invoiced', '<', 'quantity_ordered')
            ->exists();
        $allShipped = ! $salesOrder->items()
            ->whereColumn('quantity_shipped', '<', 'quantity_ordered')
            ->exists();

        $salesOrder->update([
            'status' => $allInvoiced ? 'invoiced' : ($allShipped ? 'shipped' : 'partial'),
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        $nextNumber = ((int) SalesInvoice::max('id')) + 1;

        return 'INV-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
