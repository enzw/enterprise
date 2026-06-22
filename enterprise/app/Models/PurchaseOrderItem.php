<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_id', 'department_id',
        'quantity_ordered', 'quantity_received', 'quantity_billed',
        'unit_price', 'line_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_amount' => 'decimal:2',
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

    public function receiptItems()
    {
        return $this->hasMany(ItemReceiptItem::class);
    }

    public function billItems()
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function getRemainingReceivableQuantityAttribute(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    public function getRemainingBillableQuantityAttribute(): int
    {
        return max(0, $this->quantity_received - $this->quantity_billed);
    }
}
