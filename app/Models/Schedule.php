<?php
// app/Models/Schedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'student',
        'instructor',
        'course',
        'car',
        'start',
        'end',
        'class_type',
        'status',
        'attended',
        'lessons_completed',
        'lessons_deducted',
        'is_recurring',
        'recurring_pattern',
        'recurring_end_date',
        'notes',
        'instructor_notes',
        'school_id',
    ];

    protected $casts = [
        'start' => 'datetime',           // Use start column
        'end' => 'datetime',             // Use end column
        'attended' => 'boolean',
        'lessons_deducted' => 'integer',
        'lessons_completed' => 'integer',
        'is_recurring' => 'boolean',
        'recurring_end_date' => 'date',
    ];

    // === RELATIONSHIPS ===

    public function student()
    {
        return $this->belongsTo(User::class, 'student');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    public function car()
    {
        return $this->belongsTo(Fleet::class, 'car');
    }

    // === SCOPES ===

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

    public function scopeAttended($query)
    {
        return $query->where('attended', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('start', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    // === ACCESSORS ===

    public function getDateAttribute()
    {
        return $this->start ? $this->start->format('Y-m-d') : null;
    }

    public function getTimeAttribute()
    {
        return $this->start ? $this->start->format('H:i') : null;
    }

    public function getEndTimeAttribute()
    {
        return $this->end ? $this->end->format('H:i') : null;
    }

    public function getDurationAttribute()
    {
        if ($this->start && $this->end) {
            return $this->start->diffInMinutes($this->end);
        }
        return 0;
    }

    public function getDurationHoursAttribute()
    {
        return round($this->duration / 60, 2);
    }

    public function getFormattedDateTimeAttribute()
    {
        if ($this->start) {
            return $this->start->format('M d, Y \a\t g:i A');
        }
        return null;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'scheduled' => 'badge-primary',
            'in_progress' => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    // === HELPER METHODS ===

    public function isToday()
    {
        return $this->start ? $this->start->isToday() : false;
    }

    public function isPast()
    {
        return $this->start ? $this->start->isPast() : false;
    }

    public function isFuture()
    {
        return $this->start ? $this->start->isFuture() : false;
    }

    public function canBeModified()
    {
        return in_array($this->status, ['scheduled']) && $this->isFuture();
    }

    public function canBeMarkedAttended()
    {
        return $this->status === 'scheduled' && ($this->isToday() || $this->isPast());
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'attended' => true
        ]);
    }

    public function markAsAttended()
    {
        $this->update([
            'attended' => true,
            'status' => 'completed'
        ]);
    }


/**
 * Get the school that owns the schedule
 */
public function school()
{
    return $this->belongsTo(School::class);
}

/**
 * Scope to filter schedules by school
 */
public function scopeForSchool($query, $schoolId)
{
    return $query->where('school_id', $schoolId);
}

/**
 * Scope to filter schedules for the current user's school
 */
public function scopeForCurrentUser($query, $user)
{
    if (!$user->isSuperAdmin() && $user->school_id) {
        return $query->where('school_id', $user->school_id);
    }

    return $query;
}
}
