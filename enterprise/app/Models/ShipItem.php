<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipItem extends Model
{
    protected $fillable = [
        'order_ship_id', 'sales_order_item_id',
        'quantity_to_ship', 'quantity_shipped'
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(OrderShip::class, 'order_ship_id');
    }

    public function soItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }
}
