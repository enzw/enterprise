<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    protected $fillable = [
        'subsidiary_id', 'name', 'vendor_code', 'email', 'phone',
        'address', 'credit_limit', 'credit_used', 'payment_terms', 'is_active'
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}

class PurchaseOrder extends Model
{
    protected $fillable = [
        'subsidiary_id', 'vendor_id', 'location_id', 'created_by',
        'po_number', 'external_po_number', 'order_date', 'expected_delivery_date',
        'status', 'memo', 'subtotal', 'tax_amount', 'total'
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ItemReceipt::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_id', 'department_id',
        'quantity_ordered', 'quantity_received', 'quantity_billed',
        'unit_price', 'line_amount'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}

class ItemReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'location_id', 'created_by',
        'receipt_number', 'receipt_date', 'memo', 'status'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemReceiptItem::class);
    }
}

class ItemReceiptItem extends Model
{
    protected $fillable = [
        'item_receipt_id', 'purchase_order_item_id', 'quantity_received', 'notes'
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ItemReceipt::class, 'item_receipt_id');
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
