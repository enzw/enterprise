<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackItem extends Model
{
    protected $fillable = [
        'order_pack_id', 'sales_order_item_id',
        'quantity_to_pack', 'quantity_packed'
    ];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(OrderPack::class, 'order_pack_id');
    }

    public function soItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }
}
