<?php

declare(strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class HostelAllocation extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function hostel()
    {
        return $this->room->hostel();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->check_out_date === null;
    }

    public function isCheckedOut()
    {
        return $this->status === 'completed' && $this->check_out_date !== null;
    }

    public function getDuration()
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return null;
        }

        return $this->check_in_date->diffInDays($this->check_out_date);
    }
}
