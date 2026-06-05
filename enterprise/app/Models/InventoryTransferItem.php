<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
