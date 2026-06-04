<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = ['subsidiary_id', 'name', 'code', 'description', 'is_active'];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }
}

class Location extends Model
{
    protected $fillable = ['subsidiary_id', 'name', 'code', 'address', 'is_warehouse', 'is_active'];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}

class Account extends Model
{
    protected $fillable = ['subsidiary_id', 'number', 'name', 'type', 'balance', 'is_active'];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }
}

class TaxSchedule extends Model
{
    protected $fillable = ['name', 'rate', 'is_taxable', 'is_active'];
}
