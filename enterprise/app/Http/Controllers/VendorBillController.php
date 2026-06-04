<?php

namespace App\Http\Controllers;

use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorBillController extends Controller
{
    public function index()
    {
        $this->checkRole('ap_analyst', 'accounting_manager', 'admin');
        $bills = VendorBill::with('vendor')->paginate(20);
        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        $this->checkRole('ap_analyst', 'admin');
        $vendors = Vendor::where('is_active', true)->get();
        $purchaseOrders = PurchaseOrder::where('status', 'received')->get();
        return view('bills.create', compact('vendors', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $this->checkRole('ap_analyst', 'admin');

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'bill_number' => 'required|unique:vendor_bills',
            'reference_no' => 'required|string',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after:bill_date',
            'memo' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.line_amount' => 'required|numeric|min:0',
        ]);

        $subtotal = array_sum(array_column($validated['items'], 'line_amount'));

        $bill = VendorBill::create([
            'vendor_id' => $validated['vendor_id'],
            'purchase_order_id' => $validated['purchase_order_id'],
            'subsidiary_id' => auth()->user()->subsidiary_id ?? 1,
            'created_by' => auth()->id(),
            'bill_number' => $validated['bill_number'],
            'reference_no' => $validated['reference_no'],
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'],
            'memo' => $validated['memo'],
            'status' => 'pending_approval',
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        foreach ($validated['items'] as $item) {
            VendorBillItem::create([
                'vendor_bill_id' => $bill->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'line_amount' => $item['line_amount'],
            ]);
        }

        return redirect()->route('bills.show', $bill)->with('success', 'Bill created successfully');
    }

    public function show(VendorBill $bill)
    {
        return view('bills.show', compact('bill'));
    }

    public function approve(VendorBill $bill)
    {
        $this->checkRole('accounting_manager', 'admin');

        if ($bill->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending bills can be approved');
        }

        $bill->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Bill approved');
    }

    public function reject(VendorBill $bill)
    {
        $this->checkRole('accounting_manager', 'admin');

        if ($bill->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Only pending bills can be rejected');
        }

        $bill->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Bill rejected');
    }
}
