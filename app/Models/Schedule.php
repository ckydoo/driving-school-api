<?php
// app/Models/Schedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'instructor_id', 'course_id', 'car_id',
        'start', 'end', 'class_type', 'status', 'attended',
        'lessons_deducted', 'is_recurring', 'recurring_pattern',
        'recurring_end_date', 'notes', 'instructor_notes'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'attended' => 'boolean',
        'is_recurring' => 'boolean',
        'lessons_deducted' => 'integer',
        'recurring_end_date' => 'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Fleet::class, 'car_id');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
