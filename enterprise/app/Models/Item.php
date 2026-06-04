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

class ItemAccount extends Model
{
    protected $fillable = [
        'item_id', 'cogs_account_id', 'asset_account_id', 
        'income_account_id', 'expense_account_id', 'tax_schedule_id'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function taxSchedule(): BelongsTo
    {
        return $this->belongsTo(TaxSchedule::class);
    }
}

class InventoryStock extends Model
{
    protected $fillable = [
        'item_id', 'location_id', 'quantity_on_hand', 
        'quantity_on_order', 'quantity_reserved'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
