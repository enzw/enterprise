<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\ItemReceipt;
use App\Models\ItemReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemReceiptController extends Controller
{
    public function create(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('inventory_manager', 'admin');

        if (! in_array($purchaseOrder->status, ['approved', 'partial'], true)) {
            return redirect()->back()->with('error', 'Only approved or partially received POs can receive items');
        }

        $purchaseOrder->load('vendor', 'location', 'items.item');

        return view('item-receipt.create', compact('purchaseOrder'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('inventory_manager', 'admin');

        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|distinct|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'nullable|integer|min:0',
        ]);

        $receipt = DB::transaction(function () use ($purchaseOrder, $validated) {
            $purchaseOrder = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if (! in_array($purchaseOrder->status, ['approved', 'partial'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This purchase order can no longer receive items.',
                ]);
            }

            $linesToReceive = collect($validated['items'])
                ->filter(fn (array $line) => (int) ($line['quantity_received'] ?? 0) > 0);

            if ($linesToReceive->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a quantity for at least one item.',
                ]);
            }

            $receipt = ItemReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'location_id' => $purchaseOrder->location_id,
                'created_by' => auth()->id(),
                'receipt_number' => $this->nextReceiptNumber(),
                'receipt_date' => $validated['receipt_date'],
                'memo' => $validated['memo'] ?? null,
                'status' => 'received',
            ]);

            foreach ($linesToReceive as $submittedLine) {
                $poItem = PurchaseOrderItem::with('item')
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->lockForUpdate()
                    ->find($submittedLine['po_item_id']);

                if (! $poItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected items does not belong to this purchase order.',
                    ]);
                }

                $quantity = (int) $submittedLine['quantity_received'];
                $remaining = $poItem->quantity_ordered - $poItem->quantity_received;

                if ($quantity > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Receipt quantity for {$poItem->item->name} exceeds the remaining PO quantity ({$remaining}).",
                    ]);
                }

                ItemReceiptItem::create([
                    'item_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => $quantity,
                ]);

                $poItem->increment('quantity_received', $quantity);

                if ($poItem->item->type === 'inventory') {
                    $stock = InventoryStock::query()
                        ->where('item_id', $poItem->item_id)
                        ->where('location_id', $purchaseOrder->location_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = InventoryStock::create([
                            'item_id' => $poItem->item_id,
                            'location_id' => $purchaseOrder->location_id,
                            'quantity_on_hand' => 0,
                            'quantity_on_order' => 0,
                            'quantity_reserved' => 0,
                        ]);
                    }

                    $stock->quantity_on_hand += $quantity;
                    $stock->quantity_on_order = max(0, $stock->quantity_on_order - $quantity);
                    $stock->save();
                }
            }

            $totalOrdered = $purchaseOrder->items()->sum('quantity_ordered');
            $totalReceived = $purchaseOrder->items()->sum('quantity_received');
            $purchaseOrder->update([
                'status' => $totalReceived >= $totalOrdered ? 'received' : 'partial',
            ]);

            return $receipt;
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Item Receipt {$receipt->receipt_number} recorded successfully");
    }

    private function nextReceiptNumber(): string
    {
        $nextNumber = ((int) ItemReceipt::max('id')) + 1;

        return 'IR-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
