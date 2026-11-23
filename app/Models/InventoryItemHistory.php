<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItemHistory extends Model
{
    protected $fillable = ['quantity', 'project'];
}
