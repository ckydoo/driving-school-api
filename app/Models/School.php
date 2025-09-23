<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'website',
        'start_time',
        'end_time',
        'operating_days',
        'invitation_code',
        'status',
        'subscription_status',
        'trial_ends_at',
        'monthly_fee',
        'max_students',
        'max_instructors',
        'features',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'operating_days' => 'array',
        'features' => 'array',
        'monthly_fee' => 'decimal:2',
        'max_students' => 'integer',
        'max_instructors' => 'integer',
    ];

    // === RELATIONSHIPS ===

    /**
     * Users belonging to this school
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Students belonging to this school
     */
    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    /**
     * Instructors belonging to this school
     */
    public function instructors()
    {
        return $this->hasMany(User::class)->where('role', 'instructor');
    }

    /**
     * Admins belonging to this school
     */
    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    /**
     * Fleet vehicles belonging to this school
     */
    public function fleet()
    {
        return $this->hasMany(Fleet::class);
    }

    /**
     * Courses offered by this school
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Schedules for this school (through users)
     */
    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, User::class, 'school_id', 'student');
    }

    /**
     * Invoices for this school (through students)
     */
    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, User::class, 'school_id', 'student');
    }

    /**
     * Payments for this school (through invoices)
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            Invoice::class,
            'student', // Foreign key on invoices table (student_id)
            'invoiceId', // Foreign key on payments table
            'id', // Local key on schools table
            'id' // Local key on invoices table
        );
    }

    // === SCOPES ===

    /**
     * Scope for active schools
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for schools on trial
     */
    public function scopeTrial($query)
    {
        return $query->where('subscription_status', 'trial');
    }

    /**
     * Scope for paid schools
     */
    public function scopePaid($query)
    {
        return $query->where('subscription_status', 'active');
    }

    // === ACCESSORS ===

    /**
     * Check if school is on trial
     */
    public function getIsTrialAttribute()
    {
        return $this->subscription_status === 'trial';
    }

    /**
     * Get remaining trial days
     */
    public function getRemainingTrialDaysAttribute()
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return 0;
        }

        return max(0, $this->trial_ends_at->diffInDays(now()));
    }

    /**
     * Check if trial has expired
     */
    public function getTrialExpiredAttribute()
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    // === MUTATORS ===

    /**
     * Automatically generate slug from name
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // === HELPER METHODS ===

    /**
     * Get the school's admin user
     */
    public function getAdminUser()
    {
        return $this->users()->where('role', 'admin')->first();
    }

    /**
     * Count total students
     */
    public function getTotalStudentsCount()
    {
        return $this->students()->count();
    }

    /**
     * Count total instructors
     */
    public function getTotalInstructorsCount()
    {
        return $this->instructors()->count();
    }

    /**
     * Check if school can add more students
     */
    public function canAddStudents()
    {
        return $this->getTotalStudentsCount() < $this->max_students;
    }

    /**
     * Check if school can add more instructors
     */
    public function canAddInstructors()
    {
        return $this->getTotalInstructorsCount() < $this->max_instructors;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {
            if (empty($school->invitation_code)) {
                $school->invitation_code = self::generateUniqueInvitationCode($school->name);
            }
        });
    }

    private static function generateUniqueInvitationCode($schoolName)
    {
        // Get first 3 letters of each word in school name
        $words = explode(' ', strtoupper($schoolName));
        $prefix = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $prefix .= substr($word, 0, 3);
        }

        // Add random number
        do {
            $suffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $suffix;
        } while (self::where('invitation_code', $code)->exists());

        return $code;
    }
}
