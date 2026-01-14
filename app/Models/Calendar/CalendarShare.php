<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\Model;
use App\Models\User;

class CalendarShare extends Model
{
    protected $table = 'calendar_shares';

    protected $fillable = [
        'calendar_id',
        'user_id',
        'permission_type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'calendar_id' => 'string',
        'user_id' => 'string',
    ];

    // Relationships
    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
