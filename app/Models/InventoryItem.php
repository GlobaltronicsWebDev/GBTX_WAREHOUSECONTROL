<?php

namespace App\Models;

use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category',
    'tag_number',
    'po_number',
    'manufacturer',
    'check_in_date',
    'model',
    'screen_size',
    'item_description',
    'quantity',
    'original_quantity',
    'acu_quantity',
    'forecasted_quantity',
    'sqm',
    'location',
    'status',
    'remarks',
    'reservation_qty',
    'reservation_project',
    'reservation_remarks',
    'history_qty',
    'history_project',
    'status_qty',
    'status_particular',
    'movement_history',
    'created_by',
])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'quantity' => 'integer',
            'original_quantity' => 'integer',
            'acu_quantity' => 'integer',
            'forecasted_quantity' => 'integer',
            'reservation_qty' => 'integer',
            'history_qty' => 'integer',
            'status_qty' => 'integer',
            'movement_history' => 'array',
            'sqm' => 'decimal:2',
        ];
    }

    /**
     * The user who recorded or created this inventory item.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to search inventory items by keyword.
     *
     * @param  Builder<InventoryItem>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('model', 'like', "%{$term}%")
                ->orWhere('manufacturer', 'like', "%{$term}%")
                ->orWhere('item_description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhere('tag_number', 'like', "%{$term}%")
                ->orWhere('po_number', 'like', "%{$term}%");
        });
    }
}
