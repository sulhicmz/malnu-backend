<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class ThreadParticipant extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'thread_id',
        'user_id',
        'is_admin',
        'is_muted',
        'last_read_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_muted' => 'boolean',
        'last_read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function thread()
    {
        return $this->belongsTo(MessageThread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}