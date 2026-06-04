<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Location;
use App\Models\Department;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $this->checkRole('purchasing_manager', 'inventory_manager', 'admin');
        $purchaseOrders = PurchaseOrder::with('vendor')->paginate(20);
        return view('po.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->checkRole('purchasing_manager', 'admin');
        $vendors = Vendor::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();
        $departments = Department::all();
        return view('po.create', compact('vendors', 'locations', 'items', 'departments'));
    }

    public function store(Request $request)
    {
        $this->checkRole('purchasing_manager', 'admin');

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'location_id' => 'required|exists:locations,id',
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate PO Number
        $poCount = PurchaseOrder::count() + 1;
        $poNumber = 'PO-' . str_pad($poCount, 6, '0', STR_PAD_LEFT);

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'vendor_id' => $validated['vendor_id'],
            'location_id' => $validated['location_id'],
            'subsidiary_id' => $validated['subsidiary_id'],
            'created_by' => auth()->id(),
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'],
            'memo' => $validated['memo'],
            'status' => 'draft',
        ]);

        // Add items and calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $lineAmount = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineAmount;

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_id' => $item['item_id'],
                'department_id' => $item['department_id'] ?? null,
                'quantity_ordered' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_amount' => $lineAmount,
            ]);
        }

        $po->update(['subtotal' => $subtotal, 'total' => $subtotal]);

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase Order created successfully');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return view('po.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');
        
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Can only edit draft purchase orders');
        }

        $vendors = Vendor::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();
        $departments = Department::all();

        return view('po.edit', compact('purchaseOrder', 'vendors', 'locations', 'items', 'departments'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Can only edit draft purchase orders');
        }

        $validated = $request->validate([
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'memo' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order updated successfully');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->checkRole('purchasing_manager', 'admin');

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft POs can be approved');
        }

        $purchaseOrder->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Purchase Order approved');
    }
}
