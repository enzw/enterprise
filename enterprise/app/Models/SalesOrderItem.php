<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'item_id', 'quantity_ordered', 'quantity_fulfilled',
        'quantity_packed', 'quantity_shipped', 'quantity_invoiced',
        'unit_price', 'line_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
