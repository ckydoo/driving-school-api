<?php
// app/Models/Schedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'student',
        'instructor',
        'course',
        'vehicle',
        'lesson_date',
        'start',
        'end',
        'class_type',
        'status',
        'attended',
        'lessons_deducted',
        'is_recurring',
        'recurring_pattern',
        'recurring_end_date',
        'notes',
        'instructor_notes'
    ];

    protected $casts = [
        'lesson_date' => 'datetime',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'attended' => 'boolean',
        'lessons_deducted' => 'integer',
        'is_recurring' => 'boolean',
        'recurring_end_date' => 'date',
    ];

    // Relationships
    public function studentUser()
    {
        return $this->belongsTo(User::class, 'student');
    }

    public function instructorUser()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

    public function courseInfo()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    public function vehicleInfo()
    {
        return $this->belongsTo(Fleet::class, 'vehicle');
    }


    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('lesson_date', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('lesson_date', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeAttended($query)
    {
        return $query->where('attended', true);
    }
}
