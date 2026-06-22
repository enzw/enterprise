<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemAccount;
use App\Models\Account;
use App\Models\TaxSchedule;
use App\Models\Subsidiary;
use App\Models\InventoryStock;
use App\Models\Location;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $this->checkRole('purchasing_manager', 'admin');
        $items = Item::paginate(20);
        return view('items.index', compact('items'));
    }

    public function create()
    {
        $this->checkRole('purchasing_manager', 'admin');
        $subsidiaries = Subsidiary::all();
        $accounts = Account::all();
        $taxSchedules = TaxSchedule::all();
        return view('items.create', compact('subsidiaries', 'accounts', 'taxSchedules'));
    }

    public function store(Request $request)
    {
        $this->checkRole('purchasing_manager', 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|unique:items|string|max:100',
            'type' => 'required|in:inventory,non_inventory,service',
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'description' => 'nullable|string',
            'units_type' => 'required|string',
            'base_price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'cogs_account_id' => 'nullable|exists:accounts,id',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'income_account_id' => 'required|exists:accounts,id',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'tax_schedule_id' => 'nullable|exists:tax_schedules,id',
        ]);

        $item = Item::create($validated);

        $itemAccount = [
            'item_id' => $item->id,
            'cogs_account_id' => $validated['cogs_account_id'] ?? null,
            'asset_account_id' => $validated['asset_account_id'] ?? null,
            'income_account_id' => $validated['income_account_id'],
            'expense_account_id' => $validated['expense_account_id'] ?? null,
            'tax_schedule_id' => $validated['tax_schedule_id'] ?? null,
        ];

        ItemAccount::create($itemAccount);

        return redirect()->route('items.index')->with('success', 'Item created successfully');
    }

    public function edit(Item $item)
    {
        $this->checkRole('purchasing_manager', 'admin');
        $subsidiaries = Subsidiary::all();
        $accounts = Account::all();
        $taxSchedules = TaxSchedule::all();
        return view('items.edit', compact('item', 'subsidiaries', 'accounts', 'taxSchedules'));
    }

    public function update(Request $request, Item $item)
    {
        $this->checkRole('purchasing_manager', 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|unique:items,sku,' . $item->id . '|string|max:100',
            'type' => 'required|in:inventory,non_inventory,service',
            'description' => 'nullable|string',
            'units_type' => 'required|string',
            'base_price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'income_account_id' => 'required|exists:accounts,id',
            'tax_schedule_id' => 'nullable|exists:tax_schedules,id',
        ]);

        $item->update($validated);

        if ($item->accounts) {
            $item->accounts->update([
                'income_account_id' => $validated['income_account_id'],
                'tax_schedule_id' => $validated['tax_schedule_id'] ?? null,
            ]);
        }

        return redirect()->route('items.index')->with('success', 'Item updated successfully');
    }

    // Admin can view all stocks for an item
    public function manageStocks(Item $item)
    {
        $this->checkRole('admin');
        $locations = Location::all();
        $stocks = [];
        foreach ($locations as $loc) {
            $stock = InventoryStock::firstOrCreate([
                'item_id' => $item->id,
                'location_id' => $loc->id,
            ], ['quantity_on_hand' => 0]);
            $stocks[] = ['location' => $loc, 'stock' => $stock];
        }
        return view('items.manage_stocks', compact('item', 'stocks'));
    }

    // Admin can edit stock for a specific location
    public function editStock(Item $item, Location $location)
    {
        $this->checkRole('admin');
        $stock = InventoryStock::firstOrCreate([
            'item_id' => $item->id,
            'location_id' => $location->id,
        ], ['quantity_on_hand' => 0]);
        return view('items.edit_stock', compact('item', 'stock', 'location'));
    }

    public function updateStock(Request $request, Item $item, Location $location)
    {
        $this->checkRole('admin');
        $validated = $request->validate([
            'quantity_on_hand' => 'required|integer|min:0',
        ]);
        $stock = InventoryStock::firstOrCreate([
            'item_id' => $item->id,
            'location_id' => $location->id,
        ]);
        $stock->update(['quantity_on_hand' => $validated['quantity_on_hand']]);
        return redirect()->route('items.manageStocks', $item)->with('success', 'Stock updated successfully');
    }


}
