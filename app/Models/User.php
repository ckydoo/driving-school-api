<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'gender',
        'date_of_birth',
        'phone',
        'idnumber',
        'address',
        'course',
        'role',
        'is_super_admin',
        'courseIds',
        'status',
        'school_id',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login' => 'datetime',
        'is_super_admin' => 'boolean',
        'courseIds' => 'array',
    ];

    // === RELATIONSHIPS ===

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function studentSchedules()
    {
        return $this->hasMany(Schedule::class, 'student');
    }

    public function instructorSchedules()
    {
        return $this->hasMany(Schedule::class, 'instructor');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'student');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'userId');
    }

    public function assignedVehicles()
    {
        return $this->hasMany(Fleet::class, 'instructor');
    }

    // === ACCESSORS ===

    /**
     * Get the user's full name.
     * 
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->fname} {$this->lname}");
    }

    /**
     * Get the user's display name (same as full name for compatibility).
     * 
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get a formatted role display name.
     * 
     * @return string
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'School Administrator',
            'instructor' => 'Instructor',
            'student' => 'Student',
            default => ucfirst($this->role)
        };
    }

    // === ROLE HELPER METHODS ===

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin || $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === 'admin' && !$this->is_super_admin;
    }

    public function canManageAllSchools(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageSchool($schoolId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isSchoolAdmin()) {
            return $schoolId ? $this->school_id == $schoolId : true;
        }

        return false;
    }

    // === SCOPES ===

    public function scopeForSchool(Builder $query, $schoolId = null)
    {
        if ($schoolId) {
            return $query->where('school_id', $schoolId);
        }
        return $query;
    }

    public function scopeSuperAdmins(Builder $query)
    {
        return $query->where('is_super_admin', true)
                    ->orWhere('role', 'super_admin');
    }

    public function scopeSchoolAdmins(Builder $query)
    {
        return $query->where('role', 'admin')
                    ->where('is_super_admin', false);
    }

    public function scopeInstructors(Builder $query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeStudents(Builder $query)
    {
        return $query->where('role', 'student');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    // === HELPER METHODS ===

    public function getCanAccessAdminPanelAttribute(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    // === BOOT METHOD ===

    protected static function booted()
    {
        // Auto-set is_super_admin when role is super_admin
        static::creating(function ($user) {
            if ($user->role === 'super_admin') {
                $user->is_super_admin = true;
                $user->school_id = null; // Super admins don't belong to specific schools
            }
        });

        static::updating(function ($user) {
            if ($user->role === 'super_admin') {
                $user->is_super_admin = true;
                $user->school_id = null;
            } elseif ($user->role === 'admin' && $user->is_super_admin) {
                $user->is_super_admin = false;
            }
        });
    }
}