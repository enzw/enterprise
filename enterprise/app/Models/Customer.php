<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'subsidiary_id', 'name', 'customer_code', 'email', 'phone',
        'address', 'city', 'state', 'country', 'credit_limit',
        'credit_used', 'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_used' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
