<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'fname', 'lname', 'email', 'password', 'date_of_birth',
        'role', 'status', 'gender', 'phone', 'address', 'idnumber',
        'profile_picture', 'emergency_contact'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'emergency_contact' => 'array',
        'password' => 'hashed',
    ];

    // Relationships
    public function studentSchedules()
    {
        return $this->hasMany(Schedule::class, 'student_id');
    }

    public function instructorSchedules()
    {
        return $this->hasMany(Schedule::class, 'instructor_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'student_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function assignedVehicles()
    {
        return $this->hasMany(Fleet::class, 'assigned_instructor_id');
    }

    // Scopes
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->fname . ' ' . $this->lname;
    }
}
