<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class AlumniProfile extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'student_id',
        'user_id',
        'graduation_year',
        'degree',
        'field_of_study',
        'bio',
        'public_profile',
        'allow_contact',
        'privacy_consent',
    ];

    protected $casts = [
        'public_profile'    => 'boolean',
        'allow_contact'      => 'boolean',
        'privacy_consent'    => 'boolean',
        'graduation_year'    => 'date',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function careers()
    {
        return $this->hasMany(AlumniCareer::class, 'alumni_id');
    }

    public function achievements()
    {
        return $this->hasMany(AlumniAchievement::class, 'alumni_id');
    }

    public function mentorshipsAsMentor()
    {
        return $this->hasMany(AlumniMentorship::class, 'alumni_id');
    }

    public function donations()
    {
        return $this->hasMany(AlumniDonation::class, 'alumni_id');
    }

    public function events()
    {
        return $this->belongsToMany(AlumniEvent::class, 'alumni_event_registrations', 'alumni_id', 'event_id')
            ->withPivot('attendance_status', 'registration_time', 'notes');
    }

    public function scopePublicProfiles($query)
    {
        return $query->where('public_profile', true);
    }

    public function scopeAllowContact($query)
    {
        return $query->where('allow_contact', true);
    }

    public function scopeByGraduationYear($query, $year)
    {
        return $query->where('graduation_year', $year);
    }

    public function scopeByFieldOfStudy($query, $field)
    {
        return $query->where('field_of_study', 'like', "%{$field}%");
    }
}
