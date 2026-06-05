<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
