<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransactionLine extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'warehouse_location_id',
        'product_sku',
        'product_name',
        'warehouse_location_name',
        'quantity',
        'unit_of_measurement',
        'stock_before',
        'stock_after',
    ];

    /**
     * The inventory record moved by this line, if it still exists.
     *
     * @return BelongsTo<InventoryItem, $this>
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * The warehouse assigned to the inventory record, if it still exists.
     *
     * @return BelongsTo<WarehouseLocation, $this>
     */
    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }
}
