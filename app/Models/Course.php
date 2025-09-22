<?php
// app/Models/Course.php - Complete Version

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'status',
        'type',
        'school_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'lessons_included' => 'integer',
        'school_id' => 'integer',
    ];

    // === RELATIONSHIPS ===

    /**
     * Get the school this course belongs to
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get schedules for this course
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'course');
    }

    /**
     * Get invoices for this course
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'course');
    }

    /**
     * Get students enrolled in this course through schedules
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'schedules', 'course', 'student')
                    ->where('role', 'student')
                    ->distinct();
    }

    /**
     * Get instructors teaching this course through schedules
     */
    public function instructors()
    {
        return $this->belongsToMany(User::class, 'schedules', 'course', 'instructor')
                    ->where('role', 'instructor')
                    ->distinct();
    }

    // === SCOPES ===

    /**
     * Scope for active courses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive courses
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for practical courses
     */
    public function scopePractical($query)
    {
        return $query->where('type', 'practical');
    }

    /**
     * Scope for theory courses
     */
    public function scopeTheory($query)
    {
        return $query->where('type', 'theory');
    }

    /**
     * Scope for combined courses
     */
    public function scopeCombined($query)
    {
        return $query->where('type', 'combined');
    }

    /**
     * Scope for school-specific courses
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for current user's school (if not super admin)
     */
    public function scopeForCurrentUser($query, $user)
    {
        if (!$user->isSuperAdmin() && $user->school_id) {
            return $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhereNull('school_id'); // Temporary: include unassigned courses
            });
        }
        
        return $query;
    }

    // === ACCESSORS & MUTATORS ===

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'practical' => 'primary',
            'theory' => 'info',
            'combined' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Check if course is active
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Get total revenue from this course
     */
    public function getTotalRevenueAttribute()
    {
        return $this->invoices()->where('status', 'paid')->sum('total_amount') ?? 0;
    }

    /**
     * Get total students enrolled
     */
    public function getTotalStudentsAttribute()
    {
        return $this->students()->count();
    }

    /**
     * Get total lessons scheduled
     */
    public function getTotalLessonsAttribute()
    {
        return $this->schedules()->count();
    }

    /**
     * Get completed lessons
     */
    public function getCompletedLessonsAttribute()
    {
        return $this->schedules()->where('status', 'completed')->count();
    }

    // === BOOT METHOD ===

    /**
     * Boot method to handle automatic assignments
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($course) {
            // Auto-assign school_id if not set
            if (!$course->school_id) {
                $user = auth()->user();
                if ($user && $user->school_id && !$user->isSuperAdmin()) {
                    $course->school_id = $user->school_id;
                }
            }
        });
    }
}