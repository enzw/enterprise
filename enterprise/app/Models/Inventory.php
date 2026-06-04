<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'subsidiary_id', 'location_id', 'created_by',
        'adjustment_account_id', 'reference_no', 'memo', 'status', 'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adjustmentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'adjustment_account_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'adjustment_id');
    }
}

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'adjustment_id', 'item_id', 'quantity_change', 'unit_price'
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

class InventoryTransfer extends Model
{
    protected $fillable = [
        'subsidiary_id', 'from_location_id', 'to_location_id',
        'created_by', 'reference_no', 'status',
        'shipped_at', 'received_at'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }
}

class InventoryTransferItem extends Model
{
    protected $fillable = [
        'transfer_id', 'item_id', 'quantity_requested',
        'quantity_shipped', 'quantity_received'
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
