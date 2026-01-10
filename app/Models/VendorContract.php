<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class VendorContract extends Model
{
    protected $table = 'vendor_contracts';

    protected $fillable = [
        'vendor_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'service_type',
        'contract_number',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'manager_id',
        'terms_and_conditions',
        'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    public function manager()
    {
        return $this->belongsTo(Staff::class, 'manager_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30));
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }
}
