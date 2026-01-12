<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Model;

class ParentVolunteerOpportunity extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'location',
        'slots_available',
        'slots_filled',
        'status',
        'requirements',
    ];

    protected $casts = [
        'event_date' => 'date',
        'slots_available' => 'integer',
        'slots_filled' => 'integer',
        'requirements' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function signups()
    {
        return $this->hasMany(ParentVolunteerSignup::class, 'opportunity_id');
    }

    public function availableSlots()
    {
        return $this->slots_available - $this->slots_filled;
    }
}
