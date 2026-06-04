<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index()
    {
        $this->checkRole('sales_representative', 'sales_manager', 'admin');
        $salesOrders = SalesOrder::with('customer')->paginate(20);
        return view('sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $this->checkRole('sales_representative', 'admin');
        $customers = Customer::where('is_active', true)->get();
        $items = Item::where('is_active', true)->whereIn('type', ['inventory', 'non_inventory', 'service'])->get();
        return view('sales-orders.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        $this->checkRole('sales_representative', 'admin');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date|after:order_date',
            'currency_code' => 'required|string|max:3',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate SO Number
        $soCount = SalesOrder::count() + 1;
        $soNumber = 'SO-' . str_pad($soCount, 6, '0', STR_PAD_LEFT);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $so = SalesOrder::create([
            'so_number' => $soNumber,
            'customer_id' => $validated['customer_id'],
            'subsidiary_id' => $validated['subsidiary_id'],
            'created_by' => auth()->id(),
            'order_date' => $validated['order_date'],
            'requested_delivery_date' => $validated['requested_delivery_date'],
            'currency_code' => $validated['currency_code'],
            'memo' => $validated['memo'],
            'status' => 'draft',
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        foreach ($validated['items'] as $item) {
            $lineAmount = $item['quantity'] * $item['unit_price'];
            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'item_id' => $item['item_id'],
                'quantity_ordered' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_amount' => $lineAmount,
            ]);
        }

        return redirect()->route('sales-orders.show', $so)->with('success', 'Sales Order created successfully');
    }

    public function show(SalesOrder $salesOrder)
    {
        return view('sales-orders.show', compact('salesOrder'));
    }

    public function requestApproval(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_representative', 'admin');

        if ($salesOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft orders can be submitted for approval');
        }

        $salesOrder->update(['status' => 'pending_approval']);

        return redirect()->back()->with('success', 'Order submitted for approval');
    }

    public function approve(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_manager', 'admin');

        if ($salesOrder->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending orders can be approved');
        }

        $salesOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Sales Order approved');
    }

    public function reject(SalesOrder $salesOrder)
    {
        $this->checkRole('sales_manager', 'admin');

        if ($salesOrder->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending orders can be rejected');
        }

        $salesOrder->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Sales Order rejected');
    }
}
