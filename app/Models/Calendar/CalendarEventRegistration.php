<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class CalendarEventRegistration extends Model
{
    use UsesUuid;

    public $incrementing = false;

    protected $table = 'calendar_event_registrations';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'registration_date',
        'confirmation_date',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'registration_date' => 'datetime',
        'confirmation_date' => 'datetime',
        'event_id' => 'string',
        'user_id' => 'string',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
