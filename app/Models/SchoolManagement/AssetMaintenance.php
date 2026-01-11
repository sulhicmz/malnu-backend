<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class AssetMaintenance extends Model
{

    protected $table = 'asset_maintenance';



    protected $fillable = [
        'asset_id',
        'maintenance_date',
        'maintenance_type',
        'description',
        'cost',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(SchoolInventory::class, 'asset_id');
    }
}
