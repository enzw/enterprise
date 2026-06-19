<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $this->checkRole('purchasing_manager', 'inventory_manager', 'admin');

        $purchaseOrders = PurchaseOrder::with('vendor')->latest()->paginate(20);

        return view('po.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->checkRole('purchasing_manager', 'admin');

        return view('po.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->checkRole('purchasing_manager', 'admin');
        $validated = $this->validatePurchaseOrder($request);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $this->validateMasterData($validated);

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'vendor_id' => $validated['vendor_id'],
                'location_id' => $validated['location_id'],
                'subsidiary_id' => $validated['subsidiary_id'],
                'created_by' => auth()->id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'status' => 'draft',
            ]);

            $this->syncItems($purchaseOrder, $validated['items']);

            return $purchaseOrder;
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order created successfully');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'inventory_manager', 'ap_analyst', 'accounting_manager', 'admin');

        $purchaseOrder->load([
            'vendor',
            'location',
            'createdBy',
            'items.item',
            'items.department',
            'receipts.items.poItem.item',
            'receipts.createdBy',
            'bills.payments',
        ]);

        return view('po.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be edited');
        }

        $purchaseOrder->load('items.item', 'items.department', 'vendor', 'location');

        return view('po.edit', array_merge(
            ['purchaseOrder' => $purchaseOrder],
            $this->formData()
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');
        $validated = $this->validatePurchaseOrder($request);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            $purchaseOrder = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if ($purchaseOrder->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft purchase orders can be edited.',
                ]);
            }

            $this->validateMasterData($validated);

            $purchaseOrder->update([
                'vendor_id' => $validated['vendor_id'],
                'location_id' => $validated['location_id'],
                'subsidiary_id' => $validated['subsidiary_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'memo' => $validated['memo'] ?? null,
            ]);

            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $validated['items']);
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order updated successfully');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder = PurchaseOrder::with('items.item')
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            if ($purchaseOrder->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft purchase orders can be approved.',
                ]);
            }

            foreach ($purchaseOrder->items as $line) {
                if ($line->item->type !== 'inventory') {
                    continue;
                }

                $stock = InventoryStock::query()
                    ->where('item_id', $line->item_id)
                    ->where('location_id', $purchaseOrder->location_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = InventoryStock::create([
                        'item_id' => $line->item_id,
                        'location_id' => $purchaseOrder->location_id,
                        'quantity_on_hand' => 0,
                        'quantity_on_order' => 0,
                        'quantity_reserved' => 0,
                    ]);
                }

                $stock->increment('quantity_on_order', $line->quantity_ordered);
            }

            $purchaseOrder->update(['status' => 'approved']);
        });

        return redirect()->back()->with('success', 'Purchase Order approved and inventory marked as on order');
    }

    private function formData(): array
    {
        return [
            'vendors' => Vendor::where('is_active', true)->with('subsidiary')->get(),
            'locations' => Location::where('is_active', true)->get(),
            'items' => Item::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ];
    }

    private function validatePurchaseOrder(Request $request): array
    {
        return $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'location_id' => 'required|exists:locations,id',
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|distinct|exists:items,id',
            'items.*.department_id' => 'nullable|exists:departments,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function validateMasterData(array $validated): void
    {
        $vendor = Vendor::findOrFail($validated['vendor_id']);
        $location = Location::findOrFail($validated['location_id']);
        $items = Item::whereIn('id', collect($validated['items'])->pluck('item_id'))->get();

        if ((int) $vendor->subsidiary_id !== (int) $validated['subsidiary_id']) {
            throw ValidationException::withMessages([
                'vendor_id' => 'The vendor does not belong to the selected subsidiary.',
            ]);
        }

        if ((int) $location->subsidiary_id !== (int) $validated['subsidiary_id']) {
            throw ValidationException::withMessages([
                'location_id' => 'The location does not belong to the selected subsidiary.',
            ]);
        }

        if ($items->contains(fn (Item $item) => (int) $item->subsidiary_id !== (int) $validated['subsidiary_id'])) {
            throw ValidationException::withMessages([
                'items' => 'Every item must belong to the same subsidiary as the vendor and location.',
            ]);
        }
    }

    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $lineAmount = round($item['quantity'] * $item['unit_price'], 2);
            $subtotal += $lineAmount;

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'item_id' => $item['item_id'],
                'department_id' => $item['department_id'] ?? null,
                'quantity_ordered' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_amount' => $lineAmount,
            ]);
        }

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }

    private function nextPoNumber(): string
    {
        $nextNumber = ((int) PurchaseOrder::max('id')) + 1;

        return 'PO-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
