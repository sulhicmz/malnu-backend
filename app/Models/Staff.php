<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'position',
        'department',
        'join_date',
        'status',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluations()
    {
        return $this->hasMany(StaffEvaluation::class, 'staff_id');
    }

    public function professionalDevelopment()
    {
        return $this->hasMany(ProfessionalDevelopment::class, 'staff_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
