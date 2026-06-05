<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'subsidiary_id', 'name', 'sku', 'type', 'description', 
        'units_type', 'base_price', 'purchase_price', 'reorder_level', 
        'reorder_quantity', 'is_active'
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function accounts(): HasOne
    {
        return $this->hasOne(ItemAccount::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
