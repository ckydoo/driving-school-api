<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',       // ✅ CRITICAL FIX: Add school_id to fillable
        'invoice_number',
        'student',         // Foreign key to users table
        'course',          // Foreign key to courses table
        'lessons',
        'price_per_lesson',
        'total_amount',
        'amountpaid',
        'due_date',
        'status',
        'notes',
        'used_lessons',
        'courseName',      // Backup field for course name
    ];

    protected $casts = [
        'school_id' => 'integer',           // ✅ Add cast for school_id
        'student' => 'integer',             // ✅ Add cast for foreign keys
        'course' => 'integer',
        'lessons' => 'integer',
        'used_lessons' => 'integer',
        'price_per_lesson' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amountpaid' => 'decimal:2',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship to Student (User model)
     * Fixed to use correct foreign key column name
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student', 'id')
                    ->withDefault([
                        'fname' => 'Unknown',
                        'lname' => '',
                        'email' => 'No email',
                        'phone' => null,
                    ]);
    }

    /**
     * Relationship to Course
     * Fixed to use correct foreign key column name
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course', 'id')
                    ->withDefault([
                        'name' => 'Unknown Course',
                        'description' => null,
                        'price' => 0,
                        'status' => 'inactive',
                    ]);
    }

    /**
     * Relationship to Payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoiceId', 'id');
    }

    // === SCOPES ===

    /**
     * Scope to filter by school
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'paid');
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
    }

    // === ACCESSORS & COMPUTED PROPERTIES ===

    /**
     * Get the remaining balance
     */
    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->amountpaid;
    }

    /**
     * Check if invoice is paid
     */
    public function getIsPaidAttribute()
    {
        return $this->balance <= 0;
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && !$this->is_paid;
    }

    /**
     * Get remaining lessons
     */
    public function getRemainingLessonsAttribute()
    {
        return $this->lessons - $this->used_lessons;
    }

    /**
     * Get formatted due date
     */
    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('M j, Y') : 'No due date';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'paid' => 'badge-success',
            'pending' => 'badge-warning',
            'overdue' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-primary',
        };
    }
}