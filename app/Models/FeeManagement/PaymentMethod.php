<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'requires_online_payment',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_online_payment' => 'boolean',
        'configuration' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('requires_online_payment', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('requires_online_payment', false);
    }
}
