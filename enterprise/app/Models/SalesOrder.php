<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'subsidiary_id', 'customer_id', 'created_by', 'approved_by',
        'so_number', 'po_reference', 'order_date', 'requested_delivery_date',
        'status', 'currency_code', 'subtotal', 'tax_amount', 'total',
        'memo', 'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function picks(): HasMany
    {
        return $this->hasMany(OrderPick::class);
    }

    public function packs(): HasMany
    {
        return $this->hasMany(OrderPack::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(OrderShip::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
