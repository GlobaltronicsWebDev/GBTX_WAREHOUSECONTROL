<?php

namespace App\Models;

use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tag_number',
    'date_received',
    'po_sku_number',
    'manufacturer',
    'model_pixel_pitch',
    'item_description',
    'unit_of_measurement',
    'location',
    'warehouse_location_id',
    'sub_location',
    'on_hand',
    'total_on_hand',
    'per_panel_sqm',
    'total_available_sqm',
    'available_qty',
    'available_sqm',
    'reservation_qty',
    'reservation_project_details',
    'history_qty',
    'history_project',
    'original_qty',
    'status_qty',
    'status_particular',
    'remarks',
    'created_by',
])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_received' => 'date',
            'per_panel_sqm' => 'decimal:2',
            'total_available_sqm' => 'decimal:2',
            'available_sqm' => 'decimal:2',
        ];
    }

    /**
     * Limit the list to items matching a search term.
     */
    #[Scope]
    protected function matching(Builder $query, ?string $search): void
    {
        if ($search === null || trim($search) === '') {
            return;
        }

        $term = trim($search);

        $query->where(function (Builder $query) use ($term): void {
            $query
                ->where('tag_number', 'like', "%{$term}%")
                ->orWhere('po_sku_number', 'like', "%{$term}%")
                ->orWhere('manufacturer', 'like', "%{$term}%")
                ->orWhere('model_pixel_pitch', 'like', "%{$term}%")
                ->orWhere('item_description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('sub_location', 'like', "%{$term}%");
        });
    }

    /**
     * The user who entered the item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The warehouse where the item is stored.
     */
    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    /**
     * @return HasMany<InventoryItemHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(InventoryItemHistory::class)->orderBy('id');
    }

    /**
     * Stock movement lines recorded for this inventory item.
     *
     * @return HasMany<StockTransactionLine, $this>
     */
    public function stockTransactionLines(): HasMany
    {
        return $this->hasMany(StockTransactionLine::class);
    }
}
