<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class ParentStudentRelationship extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'student_id',
        'relationship_type',
        'is_primary_contact',
        'has_custody',
        'contact_preferences',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'has_custody' => 'boolean',
        'contact_preferences' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
