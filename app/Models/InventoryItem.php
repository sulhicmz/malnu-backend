<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'code',
        'category',
        'type',
        'quantity',
        'minimum_quantity',
        'unit',
        'unit_cost',
        'location',
        'condition',
        'purchase_date',
        'last_maintenance',
        'responsible_staff_id',
        'status',
        'specifications',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'purchase_date' => 'date',
        'last_maintenance' => 'date',
    ];

    public function responsibleStaff()
    {
        return $this->belongsTo(Staff::class, 'responsible_staff_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<=', 'minimum_quantity');
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('last_maintenance', '<=', now()->subMonths(6));
    }
}
