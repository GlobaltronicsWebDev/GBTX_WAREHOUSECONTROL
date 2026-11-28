<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReceiptLine extends Model
{
    protected $fillable = [
        'inventory_item_id', 'product_sku', 'product_name', 'model_serial',
        'quantity', 'unit_of_measurement',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class, 'delivery_receipt_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
