<?php

namespace App\Models;

use App\Enums\ItemReceiptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ItemReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'location_id', 'created_by',
        'receipt_number', 'receipt_date', 'memo', 'status', 'posting_status',
        'posted_by', 'posted_at', 'reversed_by', 'reversed_at', 'reversal_reason',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'posting_status' => ItemReceiptStatus::class,
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemReceiptItem::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}
