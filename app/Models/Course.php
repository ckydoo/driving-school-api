<?php

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
        'school_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // === RELATIONSHIPS ===

    /**
     * Course belongs to a school
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Course has many schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'course');
    }

    /**
     * Course has many invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'course');
    }

    /**
     * Students enrolled in this course (through schedules)
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'schedules', 'course', 'student')
                    ->where('role', 'student')
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
     * Scope by course type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for courses within price range
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    // === ACCESSORS ===

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get course type badge color
     */
    public function getTypeBadgeColorAttribute()
    {
        return match($this->type) {
            'theory' => 'info',
            'practical' => 'success',
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
        return $this->invoices()->sum('total_amount');
    }

    /**
     * Get total enrollments
     */
    public function getTotalEnrollmentsAttribute()
    {
        return $this->schedules()->distinct('student')->count('student');
    }

    /**
     * Get completed lessons count
     */
    public function getCompletedLessonsAttribute()
    {
        return $this->schedules()->where('status', 'completed')->count();
    }

    // === METHODS ===

    /**
     * Check if a user is enrolled in this course
     */
    public function hasStudent($studentId)
    {
        return $this->schedules()->where('student', $studentId)->exists();
    }
}
