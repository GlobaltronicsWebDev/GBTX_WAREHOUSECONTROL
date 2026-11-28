<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryReceipt extends Model
{
    protected $fillable = [
        'type', 'receipt_date', 'recipient', 'address', 'tin', 'contact_person',
        'terms', 'purchase_order_number', 'sales_order_number', 'request_number',
        'received_by', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'released_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryReceiptLine::class)->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }
}
