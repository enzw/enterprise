<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subsidiary extends Model
{
    protected $fillable = ['name', 'code', 'country', 'address', 'is_active'];
    
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
