<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\Model;

class TransportationFee extends Model
{
    protected $table = 'transportation_fees';

    protected $fillable = [
        'student_id',
        'route_id',
        'amount',
        'currency',
        'academic_year',
        'semester',
        'due_date',
        'paid_date',
        'payment_status',
        'payment_method',
        'transaction_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'student_id' => 'string',
        'route_id' => 'string',
        'amount' => 'decimal',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportationRoute::class, 'route_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
