<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickItem extends Model
{
    protected $fillable = [
        'order_pick_id', 'sales_order_item_id',
        'quantity_to_pick', 'quantity_picked'
    ];

    public function pick(): BelongsTo
    {
        return $this->belongsTo(OrderPick::class, 'order_pick_id');
    }

    public function soItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }
}
