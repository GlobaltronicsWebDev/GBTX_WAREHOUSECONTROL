<?php

namespace App\Models;

use Database\Factories\BusinessPartnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPartner extends Model
{
    /** @use HasFactory<BusinessPartnerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
