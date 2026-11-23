<?php

namespace App\Models;

use Database\Factories\WarehouseLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    public const MARIKINA_SUB_LOCATIONS = ['1st Bldg', '2Floor', 'Main: 3Flr'];

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

    /**
     * @return list<string>
     */
    public function inventorySubLocations(): array
    {
        return strtolower(trim($this->name)) === 'marikina' ? self::MARIKINA_SUB_LOCATIONS : [];
    }
}
