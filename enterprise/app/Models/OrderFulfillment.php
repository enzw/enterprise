<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPick extends Model
{
    protected $fillable = [
        'sales_order_id', 'location_id', 'created_by',
        'pick_number', 'pick_date', 'status', 'notes'
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
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
        return $this->hasMany(PickItem::class);
    }
}

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

class OrderPack extends Model
{
    protected $fillable = [
        'sales_order_id', 'created_by',
        'pack_number', 'pack_date', 'status', 'notes'
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackItem::class);
    }
}

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

class OrderShip extends Model
{
    protected $fillable = [
        'sales_order_id', 'created_by',
        'ship_number', 'ship_date', 'status',
        'carrier', 'tracking_number', 'notes'
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipItem::class);
    }
}

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
