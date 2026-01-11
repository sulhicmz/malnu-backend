<?php

declare(strict_types=1);

namespace App\Models\Cafeteria;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class MealPayment extends Model
{
    protected $table = 'meal_payments';
    protected $fillable = [
        'student_id',
        'amount',
        'subsidy_amount',
        'amount_paid',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'status',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
