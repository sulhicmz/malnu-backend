<?php

declare(strict_types=1);

namespace App\Models\FinancialManagement;

use App\Models\Model;

class FeeType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
