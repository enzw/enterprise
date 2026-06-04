<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBill extends Model
{
    protected $table = 'vendor_bills';
    protected $fillable = [
        'subsidiary_id', 'vendor_id', 'purchase_order_id', 'created_by', 'approved_by',
        'bill_number', 'reference_no', 'bill_date', 'due_date', 'status',
        'subtotal', 'tax_amount', 'total', 'amount_paid', 'memo', 'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }
}

class VendorBillItem extends Model
{
    protected $fillable = [
        'vendor_bill_id', 'purchase_order_item_id', 'item_id',
        'description', 'quantity', 'unit_price', 'line_amount'
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

class BillPayment extends Model
{
    protected $fillable = [
        'vendor_bill_id', 'ap_account_id', 'cash_account_id',
        'created_by', 'amount', 'payment_date', 'payment_method',
        'reference_no', 'memo'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function apAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
