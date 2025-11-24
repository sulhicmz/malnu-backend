<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class MessageThread extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject',
        'type',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->hasMany(ThreadParticipant::class);
    }
}