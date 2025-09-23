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
        'price_per_lesson' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amountpaid' => 'decimal:2',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

    /**
     * Calculate remaining balance
     */
    public function getBalanceAttribute(): float
    {
        return round($this->total_amount - $this->amountpaid, 2);
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === 'paid') {
            return false;
        }

        return Carbon::now()->isAfter($this->due_date) && $this->balance > 0;
    }

    /**
     * Get days overdue (0 if not overdue)
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return Carbon::now()->diffInDays($this->due_date);
    }

    /**
     * Get status with overdue check
     */
    public function getStatusDisplayAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'paid';
        }

        if ($this->is_overdue) {
            return 'overdue';
        }

        return $this->status ?? 'unpaid';
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status_display) {
            case 'paid':
                return 'badge-success';
            case 'overdue':
                return 'badge-danger';
            case 'partial':
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Update status based on payment amount
     */
    public function updateStatusFromPayments(): void
    {
        if ($this->amountpaid >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->amountpaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
                    ->where('status', '!=', 'paid')
                    ->where('total_amount', '>', DB::raw('amountpaid'));
    }

    /**
     * Scope for pending invoices
     */
    public function scopePending($query)
    {
        return $query->where('status', 'unpaid')
                    ->orWhere('status', 'partial');
    }

    /**
     * Scope for school filtering
     */
    public function scopeForSchool($query, $schoolId)
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->whereHas('student', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        });
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update status when saving
        static::saving(function ($invoice) {
            // Automatically update status based on payments
            if ($invoice->amountpaid >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } elseif ($invoice->amountpaid > 0) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'unpaid';
            }

            // Set courseName from relationship if empty
            if (empty($invoice->courseName) && $invoice->course) {
                $invoice->courseName = $invoice->course->name;
            }
        });
    }
}