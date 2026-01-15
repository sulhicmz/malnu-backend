<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;
use App\Models\User;

class Staff extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'position',
        'department',
        'join_date',
        'status',
    ];

    protected $casts = [
        'join_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
