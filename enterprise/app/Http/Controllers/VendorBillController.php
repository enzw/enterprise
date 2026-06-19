<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorBillController extends Controller
{
    public function index()
    {
        $this->checkRole('ap_analyst', 'accounting_manager', 'admin');

        $bills = VendorBill::with('vendor', 'purchaseOrder')
            ->latest()
            ->paginate(20);

        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        $this->checkRole('ap_analyst', 'admin');

        $vendors = Vendor::where('is_active', true)->get();
        $purchaseOrders = PurchaseOrder::with([
            'vendor',
            'items' => fn ($query) => $query
                ->whereColumn('quantity_received', '>', 'quantity_billed')
                ->with('item'),
        ])
            ->whereIn('status', ['partial', 'received'])
            ->whereHas('items', fn ($query) => $query->whereColumn('quantity_received', '>', 'quantity_billed'))
            ->get();

        $selectedPurchaseOrderId = request()->integer('po');

        return view('bills.create', compact('vendors', 'purchaseOrders', 'selectedPurchaseOrderId'));
    }

    public function store(Request $request)
    {
        $this->checkRole('ap_analyst', 'admin');

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'bill_number' => 'required|string|max:100|unique:vendor_bills,bill_number',
            'reference_no' => 'required|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'nullable|distinct|exists:purchase_order_items,id',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.line_amount' => 'nullable|numeric|min:0',
        ]);

        $bill = DB::transaction(function () use ($validated) {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($validated['vendor_id']);
            $purchaseOrder = null;

            if (! empty($validated['purchase_order_id'])) {
                $purchaseOrder = PurchaseOrder::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['purchase_order_id']);

                if ((int) $purchaseOrder->vendor_id !== (int) $vendor->id) {
                    throw ValidationException::withMessages([
                        'vendor_id' => 'The selected vendor does not match the purchase order.',
                    ]);
                }

                if (! in_array($purchaseOrder->status, ['partial', 'received'], true)) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => 'Only received or partially received purchase orders can be billed.',
                    ]);
                }
            }

            $billLines = $this->prepareBillLines($validated['items'], $purchaseOrder);

            if (empty($billLines)) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a bill quantity or amount for at least one line.',
                ]);
            }

            $subtotal = collect($billLines)->sum('line_amount');

            $bill = VendorBill::create([
                'vendor_id' => $vendor->id,
                'purchase_order_id' => $purchaseOrder?->id,
                'subsidiary_id' => $purchaseOrder?->subsidiary_id ?? $vendor->subsidiary_id,
                'created_by' => auth()->id(),
                'bill_number' => $validated['bill_number'],
                'reference_no' => $validated['reference_no'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'status' => 'pending_approval',
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            foreach ($billLines as $line) {
                $line['vendor_bill_id'] = $bill->id;
                VendorBillItem::create($line);

                if (! empty($line['purchase_order_item_id'])) {
                    PurchaseOrderItem::whereKey($line['purchase_order_item_id'])
                        ->increment('quantity_billed', $line['quantity']);
                }
            }

            return $bill;
        });

        return redirect()
            ->route('bills.show', $bill)
            ->with('success', 'Vendor Bill created and submitted for approval');
    }

    public function show(VendorBill $bill)
    {
        $this->checkRole('ap_analyst', 'accounting_manager', 'admin');

        $bill->load([
            'vendor',
            'subsidiary',
            'purchaseOrder',
            'createdBy',
            'approvedBy',
            'items.item',
            'payments.cashAccount',
            'payments.createdBy',
        ]);

        return view('bills.show', compact('bill'));
    }

    public function submit(VendorBill $bill)
    {
        $this->checkRole('ap_analyst', 'admin');

        if ($bill->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft bills can be submitted');
        }

        $bill->update(['status' => 'pending_approval']);

        return redirect()->back()->with('success', 'Vendor Bill submitted for approval');
    }

    public function approve(VendorBill $bill)
    {
        $this->checkRole('accounting_manager', 'admin');

        DB::transaction(function () use ($bill) {
            $bill = VendorBill::with('items.item.accounts', 'vendor')
                ->lockForUpdate()
                ->findOrFail($bill->id);

            if ($bill->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending bills can be approved.',
                ]);
            }

            $apAccount = Account::query()
                ->where('subsidiary_id', $bill->subsidiary_id)
                ->where('number', '2110')
                ->lockForUpdate()
                ->first();

            if (! $apAccount) {
                throw ValidationException::withMessages([
                    'account' => 'Accounts Payable account 2110 was not found for this subsidiary.',
                ]);
            }

            $fallbackExpenseAccount = Account::query()
                ->where('subsidiary_id', $bill->subsidiary_id)
                ->where('number', '6081')
                ->first();

            foreach ($bill->items as $line) {
                $postingAccountId = null;

                if ($line->item?->accounts) {
                    $postingAccountId = $line->item->type === 'inventory'
                        ? $line->item->accounts->asset_account_id
                        : $line->item->accounts->expense_account_id;
                }

                $postingAccountId ??= $fallbackExpenseAccount?->id;

                if (! $postingAccountId) {
                    throw ValidationException::withMessages([
                        'account' => "No inventory asset or purchase expense account is configured for {$line->description}.",
                    ]);
                }

                Account::whereKey($postingAccountId)->increment('balance', $line->line_amount);
            }

            $apAccount->increment('balance', $bill->total);
            $bill->vendor->increment('credit_used', $bill->total);

            $bill->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Vendor Bill approved and posted to Accounts Payable');
    }

    public function reject(VendorBill $bill)
    {
        $this->checkRole('accounting_manager', 'admin');

        if ($bill->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending bills can be rejected');
        }

        $bill->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Vendor Bill returned to draft');
    }

    private function prepareBillLines(array $submittedItems, ?PurchaseOrder $purchaseOrder): array
    {
        $billLines = [];

        foreach ($submittedItems as $submittedLine) {
            $quantity = (int) ($submittedLine['quantity'] ?? 0);

            if ($purchaseOrder) {
                if (empty($submittedLine['po_item_id']) || $quantity <= 0) {
                    continue;
                }

                $poItem = PurchaseOrderItem::with('item')
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->lockForUpdate()
                    ->find($submittedLine['po_item_id']);

                if (! $poItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the bill lines does not belong to the selected purchase order.',
                    ]);
                }

                $availableToBill = $poItem->quantity_received - $poItem->quantity_billed;

                if ($quantity > $availableToBill) {
                    throw ValidationException::withMessages([
                        'items' => "Bill quantity for {$poItem->item->name} exceeds the received and unbilled quantity ({$availableToBill}).",
                    ]);
                }

                $billLines[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'item_id' => $poItem->item_id,
                    'description' => "{$poItem->item->sku} - {$poItem->item->name}",
                    'quantity' => $quantity,
                    'unit_price' => $poItem->unit_price,
                    'line_amount' => round($quantity * $poItem->unit_price, 2),
                ];

                continue;
            }

            $lineAmount = round((float) ($submittedLine['line_amount'] ?? 0), 2);
            if ($lineAmount <= 0) {
                continue;
            }

            $billLines[] = [
                'purchase_order_item_id' => null,
                'item_id' => $submittedLine['item_id'] ?? null,
                'description' => $submittedLine['description'],
                'quantity' => $quantity ?: null,
                'unit_price' => $submittedLine['unit_price'] ?? null,
                'line_amount' => $lineAmount,
            ];
        }

        return $billLines;
    }
}
