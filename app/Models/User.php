<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fname',
        'lname', 
        'email',
        'gender',
        'date_of_birth',
        'phone',
        'idnumber',
        'address',
        'password',
        'course',
        'role',
        'courseIds',
        'status',
        'last_login',
        'last_login_method'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_login' => 'datetime',
        'courseIds' => 'array', // Will be stored as JSON in database
    ];

    // Relationships
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

    public function notesWritten()
    {
        return $this->hasMany(Note::class, 'note_by');
    }

    public function notesReceived()
    {
        return $this->hasMany(Note::class, 'note_for');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user');
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
        return $query->where('status', 'Active');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->fname . ' ' . $this->lname;
    }
}
