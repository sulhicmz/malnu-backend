<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\User;
use App\Models\Model;

class Calendar extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $table = 'calendars';

    protected $fillable = [
        'name',
        'description',
        'color',
        'type',
        'is_public',
        'permissions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function events()
    {
        return $this->hasMany(CalendarEvent::class, 'calendar_id');
    }

    public function shares()
    {
        return $this->hasMany(CalendarShare::class, 'calendar_id');
    }
}