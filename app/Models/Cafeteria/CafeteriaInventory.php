<?php

declare(strict_types=1);

namespace App\Models\Cafeteria;

use App\Models\Model;

class CafeteriaInventory extends Model
{
    protected $table = 'cafeteria_inventories';
    protected $fillable = [
        'item_name',
        'category',
        'quantity',
        'unit',
        'unit_cost',
        'vendor_id',
        'expiry_date',
        'minimum_stock_level',
        'notes',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
