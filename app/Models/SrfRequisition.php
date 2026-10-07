<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrfRequisition extends Model
{
    protected $fillable = [
        'srf_number',
        'sso_number',
        'project_name',
        'client',
        'po_number',
        'date_needed',
        'requisition_date',
        'inventory_item_id',
        'quantity',
        'uom',
        'stock_status',
        'status',
        'department',
        'prepared_by',
        'noted_by',
        'pre_approved_by',
        'approved_by',
        'user_id',
        'verified_by_user_id',
        'verified_by_name',
        'verified_at',
        'verification_notes',
        'notes',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'date_needed' => 'date',
            'requisition_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'verified'], true);
    }
}
