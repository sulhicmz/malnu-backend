<?php

declare (strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\Model;
use App\Models\User;

class AssetAssignment extends Model
{
    protected $table = 'asset_assignments';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'asset_id',
        'assigned_to',
        'assigned_to_type',
        'assigned_date',
        'returned_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(SchoolInventory::class, 'asset_id');
    }

    public function assignedTo()
    {
        return $this->morphTo();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
