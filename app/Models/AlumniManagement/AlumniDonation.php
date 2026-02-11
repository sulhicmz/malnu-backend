<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;

class AlumniDonation extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'alumni_id',
        'amount',
        'currency',
        'donation_type',
        'campaign',
        'anonymous',
        'acknowledged',
        'message',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'anonymous'    => 'boolean',
        'acknowledged' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('acknowledged', true);
    }

    public function scopeByCampaign($query, $campaign)
    {
        return $query->where('campaign', 'like', "%{$campaign}%");
    }

    public function scopeByType($query, $type)
    {
        return $query->where('donation_type', $type);
    }
}
