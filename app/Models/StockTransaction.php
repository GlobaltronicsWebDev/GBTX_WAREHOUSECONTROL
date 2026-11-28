<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransaction extends Model
{
    protected $fillable = [
        'reference_number',
        'type',
        'transaction_date',
        'purchase_order_number',
        'supplier_name',
        'request_number',
        'department_customer',
        'released_by',
        'received_by',
        'remarks',
        'attachment_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    /**
     * The user who recorded this transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The products recorded in this transaction.
     *
     * @return HasMany<StockTransactionLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(StockTransactionLine::class)->orderBy('id');
    }
}
