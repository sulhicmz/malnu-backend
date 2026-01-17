<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;
use App\Models\User;

class SchoolInventory extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
        'quantity',
        'location',
        'condition',
        'purchase_date',
        'last_maintenance',
        'serial_number',
        'asset_code',
        'category_id',
        'status',
        'purchase_cost',
        'assigned_to',
        'assigned_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance' => 'date',
        'purchase_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }
}
