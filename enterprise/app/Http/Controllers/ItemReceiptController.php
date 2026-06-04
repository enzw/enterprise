<?php

namespace App\Http\Controllers;

use App\Models\ItemReceipt;
use App\Models\ItemReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\InventoryStock;
use Illuminate\Http\Request;

class ItemReceiptController extends Controller
{
    public function create(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('inventory_manager', 'admin');

        if (!in_array($purchaseOrder->status, ['approved', 'partial', 'received'])) {
            return redirect()->back()->with('error', 'Can only receive approved POs');
        }

        return view('item-receipt.create', compact('purchaseOrder'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('inventory_manager', 'admin');

        if (!in_array($purchaseOrder->status, ['approved', 'partial', 'received'])) {
            return redirect()->back()->with('error', 'Can only receive approved POs');
        }

        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:1',
        ]);

        // Generate Receipt Number
        $receiptCount = ItemReceipt::count() + 1;
        $receiptNumber = 'IR-' . str_pad($receiptCount, 6, '0', STR_PAD_LEFT);

        $receipt = ItemReceipt::create([
            'purchase_order_id' => $purchaseOrder->id,
            'location_id' => $purchaseOrder->location_id,
            'created_by' => auth()->id(),
            'receipt_number' => $receiptNumber,
            'receipt_date' => $validated['receipt_date'],
            'memo' => $validated['memo'],
            'status' => 'received',
        ]);

        foreach ($validated['items'] as $item) {
            $poItem = $purchaseOrder->items()->find($item['po_item_id']);
            
            ItemReceiptItem::create([
                'item_receipt_id' => $receipt->id,
                'purchase_order_item_id' => $item['po_item_id'],
                'quantity_received' => $item['quantity_received'],
            ]);

            // Update PO Item
            $poItem->quantity_received += $item['quantity_received'];
            $poItem->save();

            // Update Inventory Stock
            $stock = InventoryStock::where('item_id', $poItem->item_id)
                ->where('location_id', $purchaseOrder->location_id)
                ->first();

            if ($stock) {
                $stock->quantity_on_hand += $item['quantity_received'];
                $stock->save();
            } else {
                InventoryStock::create([
                    'item_id' => $poItem->item_id,
                    'location_id' => $purchaseOrder->location_id,
                    'quantity_on_hand' => $item['quantity_received'],
                ]);
            }
        }

        // Update PO status
        $totalOrdered = $purchaseOrder->items()->sum('quantity_ordered');
        $totalReceived = $purchaseOrder->items()->sum('quantity_received');

        if ($totalReceived >= $totalOrdered) {
            $purchaseOrder->update(['status' => 'received']);
        } else {
            $purchaseOrder->update(['status' => 'partial']);
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Items received successfully');
    }
}
