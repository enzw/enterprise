<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\InventoryStock;
use App\Models\OrderPack;
use App\Models\OrderPick;
use App\Models\OrderShip;
use App\Models\PackItem;
use App\Models\PickItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ShipItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentController extends Controller
{
    public function createPick(SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');
        $salesOrder->load('customer', 'location', 'items.item');

        if (! $this->hasRemaining($salesOrder, 'quantity_fulfilled')) {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', 'All order quantities have already been picked');
        }

        if (! in_array($salesOrder->status, ['approved', 'partial'], true)) {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', 'This Sales Order is not ready for picking');
        }

        return view('fulfillment.pick', compact('salesOrder'));
    }

    public function storePick(Request $request, SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');
        $validated = $this->validateLines($request, 'quantity');

        $pick = DB::transaction(function () use ($salesOrder, $validated) {
            $salesOrder = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            if (! in_array($salesOrder->status, ['approved', 'partial'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This Sales Order is not ready for picking.',
                ]);
            }

            $lines = $this->positiveLines($validated['items'], 'quantity');
            $this->ensureLinesExist($lines);

            $pick = OrderPick::create([
                'sales_order_id' => $salesOrder->id,
                'location_id' => $salesOrder->location_id,
                'created_by' => auth()->id(),
                'pick_number' => $this->nextNumber(OrderPick::class, 'PICK'),
                'pick_date' => $validated['date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $submittedLine) {
                $soItem = $this->lockedSalesOrderItem($salesOrder, $submittedLine['so_item_id']);
                $quantity = (int) $submittedLine['quantity'];
                $remaining = $soItem->quantity_ordered - $soItem->quantity_fulfilled;

                if ($quantity > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Pick quantity for {$soItem->item->name} exceeds the remaining quantity ({$remaining}).",
                    ]);
                }

                if ($soItem->item->type === 'inventory') {
                    $stock = InventoryStock::query()
                        ->where('item_id', $soItem->item_id)
                        ->where('location_id', $salesOrder->location_id)
                        ->lockForUpdate()
                        ->first();

                    $available = $stock ? $stock->quantity_on_hand - $stock->quantity_reserved : 0;
                    if ($quantity > $available) {
                        throw ValidationException::withMessages([
                            'items' => "Available stock for {$soItem->item->name} is only {$available}.",
                        ]);
                    }

                    $stock->increment('quantity_reserved', $quantity);
                }

                PickItem::create([
                    'order_pick_id' => $pick->id,
                    'sales_order_item_id' => $soItem->id,
                    'quantity_to_pick' => $quantity,
                    'quantity_picked' => $quantity,
                ]);

                $soItem->increment('quantity_fulfilled', $quantity);
            }

            $salesOrder->update([
                'status' => $this->allComplete($salesOrder, 'quantity_fulfilled') ? 'fulfilled' : 'partial',
            ]);

            return $pick;
        });

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', "Pick {$pick->pick_number} completed and inventory reserved");
    }

    public function createPack(SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');
        $salesOrder->load('customer', 'location', 'items.item');

        if (! $salesOrder->items->contains(fn ($line) => $line->quantity_fulfilled > $line->quantity_packed)) {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', 'There are no picked quantities available to pack');
        }

        return view('fulfillment.pack', compact('salesOrder'));
    }

    public function storePack(Request $request, SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');
        $validated = $this->validateLines($request, 'quantity');

        $pack = DB::transaction(function () use ($salesOrder, $validated) {
            $salesOrder = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);
            $lines = $this->positiveLines($validated['items'], 'quantity');
            $this->ensureLinesExist($lines);

            $pack = OrderPack::create([
                'sales_order_id' => $salesOrder->id,
                'created_by' => auth()->id(),
                'pack_number' => $this->nextNumber(OrderPack::class, 'PACK'),
                'pack_date' => $validated['date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $submittedLine) {
                $soItem = $this->lockedSalesOrderItem($salesOrder, $submittedLine['so_item_id']);
                $quantity = (int) $submittedLine['quantity'];
                $available = $soItem->quantity_fulfilled - $soItem->quantity_packed;

                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        'items' => "Pack quantity for {$soItem->item->name} exceeds the picked and unpacked quantity ({$available}).",
                    ]);
                }

                PackItem::create([
                    'order_pack_id' => $pack->id,
                    'sales_order_item_id' => $soItem->id,
                    'quantity_to_pack' => $quantity,
                    'quantity_packed' => $quantity,
                ]);

                $soItem->increment('quantity_packed', $quantity);
            }

            $salesOrder->update([
                'status' => $this->allComplete($salesOrder, 'quantity_packed') ? 'packed' : 'partial',
            ]);

            return $pack;
        });

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', "Pack {$pack->pack_number} completed");
    }

    public function createShip(SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');
        $salesOrder->load('customer', 'location', 'items.item');

        if (! $salesOrder->items->contains(fn ($line) => $line->quantity_packed > $line->quantity_shipped)) {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', 'There are no packed quantities available to ship');
        }

        return view('fulfillment.ship', compact('salesOrder'));
    }

    public function storeShip(Request $request, SalesOrder $salesOrder)
    {
        $this->checkRole('inventory_manager', 'admin');

        $validated = $request->validate([
            'date' => 'required|date',
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.so_item_id' => 'required|distinct|exists:sales_order_items,id',
            'items.*.quantity' => 'nullable|integer|min:0',
        ]);

        $ship = DB::transaction(function () use ($salesOrder, $validated) {
            $salesOrder = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);
            $lines = $this->positiveLines($validated['items'], 'quantity');
            $this->ensureLinesExist($lines);

            $ship = OrderShip::create([
                'sales_order_id' => $salesOrder->id,
                'created_by' => auth()->id(),
                'ship_number' => $this->nextNumber(OrderShip::class, 'SHIP'),
                'ship_date' => $validated['date'],
                'status' => 'completed',
                'carrier' => $validated['carrier'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $submittedLine) {
                $soItem = $this->lockedSalesOrderItem($salesOrder, $submittedLine['so_item_id']);
                $quantity = (int) $submittedLine['quantity'];
                $available = $soItem->quantity_packed - $soItem->quantity_shipped;

                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        'items' => "Ship quantity for {$soItem->item->name} exceeds the packed and unshipped quantity ({$available}).",
                    ]);
                }

                if ($soItem->item->type === 'inventory') {
                    $stock = InventoryStock::query()
                        ->where('item_id', $soItem->item_id)
                        ->where('location_id', $salesOrder->location_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock || $stock->quantity_on_hand < $quantity || $stock->quantity_reserved < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Reserved inventory for {$soItem->item->name} is insufficient for shipment.",
                        ]);
                    }

                    $stock->quantity_on_hand -= $quantity;
                    $stock->quantity_reserved -= $quantity;
                    $stock->save();

                    $this->postCostOfGoodsSold($soItem, $quantity);
                }

                ShipItem::create([
                    'order_ship_id' => $ship->id,
                    'sales_order_item_id' => $soItem->id,
                    'quantity_to_ship' => $quantity,
                    'quantity_shipped' => $quantity,
                ]);

                $soItem->increment('quantity_shipped', $quantity);
            }

            $salesOrder->update([
                'status' => $this->allComplete($salesOrder, 'quantity_shipped') ? 'shipped' : 'partial',
            ]);

            return $ship;
        });

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', "Shipment {$ship->ship_number} completed and inventory issued");
    }

    private function validateLines(Request $request, string $quantityField): array
    {
        return $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.so_item_id' => 'required|distinct|exists:sales_order_items,id',
            "items.*.{$quantityField}" => 'nullable|integer|min:0',
        ]);
    }

    private function positiveLines(array $items, string $quantityField)
    {
        return collect($items)
            ->filter(fn (array $line) => (int) ($line[$quantityField] ?? 0) > 0)
            ->values();
    }

    private function ensureLinesExist($lines): void
    {
        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Enter a quantity for at least one item.',
            ]);
        }
    }

    private function lockedSalesOrderItem(SalesOrder $salesOrder, int $itemId): SalesOrderItem
    {
        $soItem = SalesOrderItem::with('item.accounts')
            ->where('sales_order_id', $salesOrder->id)
            ->lockForUpdate()
            ->find($itemId);

        if (! $soItem) {
            throw ValidationException::withMessages([
                'items' => 'One of the selected lines does not belong to this Sales Order.',
            ]);
        }

        return $soItem;
    }

    private function hasRemaining(SalesOrder $salesOrder, string $column): bool
    {
        return $salesOrder->items->contains(fn ($line) => $line->quantity_ordered > $line->{$column});
    }

    private function allComplete(SalesOrder $salesOrder, string $column): bool
    {
        return ! SalesOrderItem::where('sales_order_id', $salesOrder->id)
            ->whereColumn($column, '<', 'quantity_ordered')
            ->exists();
    }

    private function postCostOfGoodsSold(SalesOrderItem $soItem, int $quantity): void
    {
        $accounts = $soItem->item->accounts;

        if (! $accounts?->cogs_account_id || ! $accounts?->asset_account_id) {
            throw ValidationException::withMessages([
                'account' => "COGS and inventory asset accounts are required for {$soItem->item->name}.",
            ]);
        }

        $unitCost = (float) ($soItem->item->purchase_price ?? 0);
        $cost = round($unitCost * $quantity, 2);

        Account::whereKey($accounts->cogs_account_id)->increment('balance', $cost);
        Account::whereKey($accounts->asset_account_id)->decrement('balance', $cost);
    }

    private function nextNumber(string $modelClass, string $prefix): string
    {
        $nextNumber = ((int) $modelClass::max('id')) + 1;

        return $prefix.'-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
