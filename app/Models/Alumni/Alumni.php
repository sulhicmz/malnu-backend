<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Alumni\AlumniCareer;
use App\Models\Alumni\AlumniDonation;
use App\Models\Alumni\AlumniEventRegistration;
use App\Models\Alumni\AlumniEngagement;
use App\Models\Alumni\AlumniMentorship;
use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class Alumni extends Model
{
    protected $table = 'alumni';

    protected $fillable = [
        'student_id',
        'user_id',
        'graduation_year',
        'graduation_class',
        'degree',
        'field_of_study',
        'current_company',
        'current_position',
        'industry',
        'linkedin_url',
        'bio',
        'achievements',
        'is_verified',
        'is_public',
        'allow_contact',
        'newsletter_subscription',
        'mentor_availability',
        'privacy_settings',
        'consent_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'privacy_settings' => 'array',
        'consent_data' => 'array',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
        'allow_contact' => 'boolean',
        'newsletter_subscription' => 'boolean',
        'mentor_availability' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
        'graduation_year' => 'integer',
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

    public function donations()
    {
        return $this->hasMany(AlumniDonation::class, 'alumni_id');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(AlumniEventRegistration::class, 'alumni_id');
    }

    public function engagements()
    {
        return $this->hasMany(AlumniEngagement::class, 'alumni_id');
    }

    public function mentorshipsAsMentor()
    {
        return $this->hasMany(AlumniMentorship::class, 'mentor_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeAvailableForMentorship($query)
    {
        return $query->where('mentor_availability', true);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeByGraduationYear($query, $year)
    {
        return $query->where('graduation_year', $year);
    }

    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }

    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : null;
    }
}
