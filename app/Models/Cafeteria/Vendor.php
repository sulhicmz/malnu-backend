<?php

declare(strict_types=1);

namespace App\Models\Cafeteria;

use App\Models\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function cafeteriaInventories()
    {
        return $this->hasMany(CafeteriaInventory::class, 'vendor_id');
    }
}
