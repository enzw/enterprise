<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'subsidiary_id', 'name', 'vendor_code', 'email', 'phone',
        'address', 'credit_limit', 'credit_used', 'payment_terms', 'is_active',
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

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }
}
