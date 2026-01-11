<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Model;
use App\Models\User;

class ParentVolunteerSignup extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'opportunity_id',
        'status',
        'notes',
        'signed_up_at',
    ];

    protected $casts = [
        'signed_up_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(ParentVolunteerOpportunity::class, 'opportunity_id');
    }
}
