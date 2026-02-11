<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class AssetCategory extends Model
{
    public $incrementing = false;

    protected $table = 'asset_categories';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assets()
    {
        return $this->hasMany(SchoolInventory::class, 'category_id');
    }
}
