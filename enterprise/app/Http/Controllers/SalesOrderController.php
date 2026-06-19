<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\Location;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderController extends Controller
{
    public function index()
    {
        $this->checkRole('sales_representative', 'sales_manager', 'inventory_manager', 'ar_analyst', 'accounting_manager', 'admin');

        $salesOrders = SalesOrder::with('customer', 'location')->latest()->paginate(20);

        return view('sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $this->checkRole('sales_representative', 'admin');

        return view('sales-orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->checkRole('sales_representative', 'admin');
        $validated = $this->validateSalesOrder($request);

        $salesOrder = DB::transaction(function () use ($validated) {
            $this->validateMasterData($validated);

            $salesOrder = SalesOrder::create([
                'so_number' => $this->nextSalesOrderNumber(),
                'customer_id' => $validated['customer_id'],
                'location_id' => $validated['location_id'],
                'subsidiary_id' => $validated['subsidiary_id'],
                'created_by' => auth()->id(),
                'po_reference' => $validated['po_reference'] ?? null,
                'order_date' => $validated['order_date'],
                'requested_delivery_date' => $validated['requested_delivery_date'] ?? null,
                'currency_code' => $validated['currency_code'],
                'memo' => $validated['memo'] ?? null,
                'status' => 'draft',
            ]);

            $this->syncItems($salesOrder, $validated['items']);

            return $salesOrder;
        });

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', 'Sales Order created successfully');
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'sales_manager', 'inventory_manager', 'ar_analyst', 'accounting_manager', 'admin');

        $salesOrder->load([
            'customer',
            'subsidiary',
            'location',
            'createdBy',
            'approvedBy',
            'items.item',
            'picks.items.soItem.item',
            'picks.createdBy',
            'packs.items.soItem.item',
            'packs.createdBy',
            'ships.items.soItem.item',
            'ships.createdBy',
            'invoices',
        ]);

        return view('sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'admin');

        if ($salesOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft sales orders can be edited');
        }

        $salesOrder->load('items.item');

        return view('sales-orders.edit', array_merge(
            ['salesOrder' => $salesOrder],
            $this->formData()
        ));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'admin');
        $validated = $this->validateSalesOrder($request);

        DB::transaction(function () use ($salesOrder, $validated) {
            $salesOrder = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            if ($salesOrder->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft sales orders can be edited.',
                ]);
            }

            $this->validateMasterData($validated);

            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'location_id' => $validated['location_id'],
                'subsidiary_id' => $validated['subsidiary_id'],
                'po_reference' => $validated['po_reference'] ?? null,
                'order_date' => $validated['order_date'],
                'requested_delivery_date' => $validated['requested_delivery_date'] ?? null,
                'currency_code' => $validated['currency_code'],
                'memo' => $validated['memo'] ?? null,
            ]);

            $salesOrder->items()->delete();
            $this->syncItems($salesOrder, $validated['items']);
        });

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', 'Sales Order updated successfully');
    }

    public function requestApproval(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'admin');

        if ($salesOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft orders can be submitted for approval');
        }

        $salesOrder->update(['status' => 'pending_approval']);

        return redirect()->back()->with('success', 'Sales Order submitted for approval');
    }

    public function approve(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_manager', 'admin');

        DB::transaction(function () use ($salesOrder) {
            $salesOrder = SalesOrder::with('customer', 'items.item')
                ->lockForUpdate()
                ->findOrFail($salesOrder->id);

            if ($salesOrder->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending sales orders can be approved.',
                ]);
            }

            $availableCredit = (float) $salesOrder->customer->credit_limit - (float) $salesOrder->customer->credit_used;
            if ((float) $salesOrder->total > $availableCredit) {
                throw ValidationException::withMessages([
                    'credit' => "Customer credit is insufficient. Available credit: {$availableCredit}.",
                ]);
            }

            foreach ($salesOrder->items as $line) {
                if ($line->item->type !== 'inventory') {
                    continue;
                }

                $stock = InventoryStock::query()
                    ->where('item_id', $line->item_id)
                    ->where('location_id', $salesOrder->location_id)
                    ->lockForUpdate()
                    ->first();

                $availableStock = $stock
                    ? $stock->quantity_on_hand - $stock->quantity_reserved
                    : 0;

                if ($line->quantity_ordered > $availableStock) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient available stock for {$line->item->name}. Available: {$availableStock}.",
                    ]);
                }
            }

            $salesOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Sales Order approved and ready for fulfillment');
    }

    public function reject(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_manager', 'admin');

        if ($salesOrder->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending orders can be rejected');
        }

        $salesOrder->update([
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->back()->with('success', 'Sales Order returned to draft');
    }

    public function cancel(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'sales_manager', 'admin');

        if (! in_array($salesOrder->status, ['draft', 'pending_approval', 'approved'], true)) {
            return redirect()->back()->with('error', 'Orders with fulfillment activity cannot be cancelled');
        }

        if ($salesOrder->items()->where('quantity_fulfilled', '>', 0)->exists()) {
            return redirect()->back()->with('error', 'Orders with picked quantities cannot be cancelled');
        }

        $salesOrder->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Sales Order cancelled');
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::where('is_active', true)->with('subsidiary')->get(),
            'locations' => Location::where('is_active', true)->where('is_warehouse', true)->get(),
            'items' => Item::where('is_active', true)->get(),
        ];
    }

    private function validateSalesOrder(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'location_id' => 'required|exists:locations,id',
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'po_reference' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'currency_code' => 'required|string|size:3',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|distinct|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function validateMasterData(array $validated): void
    {
        $customer = Customer::findOrFail($validated['customer_id']);
        $location = Location::findOrFail($validated['location_id']);
        $items = Item::whereIn('id', collect($validated['items'])->pluck('item_id'))->get();

        if ((int) $customer->subsidiary_id !== (int) $validated['subsidiary_id']) {
            throw ValidationException::withMessages([
                'customer_id' => 'The customer does not belong to the selected subsidiary.',
            ]);
        }

        if ((int) $location->subsidiary_id !== (int) $validated['subsidiary_id']) {
            throw ValidationException::withMessages([
                'location_id' => 'The fulfillment location does not belong to the selected subsidiary.',
            ]);
        }

        if ($items->contains(fn (Item $item) => (int) $item->subsidiary_id !== (int) $validated['subsidiary_id'])) {
            throw ValidationException::withMessages([
                'items' => 'Every item must belong to the same subsidiary as the customer.',
            ]);
        }
    }

    private function syncItems(SalesOrder $salesOrder, array $items): void
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $lineAmount = round($item['quantity'] * $item['unit_price'], 2);
            $subtotal += $lineAmount;

            SalesOrderItem::create([
                'sales_order_id' => $salesOrder->id,
                'item_id' => $item['item_id'],
                'quantity_ordered' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_amount' => $lineAmount,
            ]);
        }

        $salesOrder->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }

    private function nextSalesOrderNumber(): string
    {
        $nextNumber = ((int) SalesOrder::max('id')) + 1;

        return 'SO-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
