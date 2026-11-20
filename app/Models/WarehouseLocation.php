<?php

namespace App\Models;

use Database\Factories\WarehouseLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    /** @use HasFactory<WarehouseLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'entity',
        'is_active',
        'navigation_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'navigation_enabled' => 'boolean',
        ];
    }
}
