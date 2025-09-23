<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'student',
        'course',
        'lessons',
        'price_per_lesson',
        'total_amount',
        'amountpaid',
        'due_date',
        'status',
        'notes',
        'school_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'lessons' => 'integer',
        'price_per_lesson' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amountpaid' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // === RELATIONSHIPS ===

    /**
     * Invoice belongs to a student (User)
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student');
    }

    /**
     * Invoice belongs to a course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    /**
     * Invoice belongs to a school (via student relationship or direct if school_id exists)
     */
    public function school()
    {
        // If you have school_id field, use direct relationship
        if (in_array('school_id', $this->fillable) && $this->school_id) {
            return $this->belongsTo(School::class, 'school_id');
        }

        // Otherwise, get school through student relationship
        return $this->student ? $this->student->school() : null;
    }
/**
 * Get school ID through student relationship safely
 */
public function getSchoolIdAttribute()
{
    // If invoice has direct school_id, use it
    if (isset($this->attributes['school_id']) && $this->attributes['school_id']) {
        return $this->attributes['school_id'];
    }

    // Otherwise get through student relationship
    return $this->student ? $this->student->school_id : null;
}
    /**
     * Invoice has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoiceId');
    }

    // === ACCESSORS & MUTATORS ===

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->amountpaid;
    }

    /**
     * Get status with fallback
     */
    public function getStatusAttribute($value)
    {
        return $value ?: 'unpaid';
    }

    /**
     * Set status
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted amount paid
     */
    public function getFormattedAmountPaidAttribute()
    {
        return '$' . number_format($this->amountpaid, 2);
    }

    /**
     * Get formatted remaining balance
     */
    public function getFormattedRemainingBalanceAttribute()
    {
        return '$' . number_format($this->remaining_balance, 2);
    }

    // === HELPER METHODS ===

    /**
     * Check if invoice is fully paid
     */
    public function isPaid()
    {
        return $this->status === 'paid' || $this->amountpaid >= $this->total_amount;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->due_date < now() && !$this->isPaid();
    }

    /**
     * Automatically update status based on payment amount
     */
    public function updateStatus()
    {
        if ($this->amountpaid >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->amountpaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
        return $this;
    }

    /**
     * Add payment to this invoice
     */
    public function addPayment($amount, $method = 'cash', $notes = null)
    {
        // Create payment record
        $payment = $this->payments()->create([
            'userId' => $this->student,
            'amount' => $amount,
            'method' => $method,
            'paymentDate' => now(),
            'status' => 'Paid',
            'notes' => $notes,
        ]);

        // Update invoice
        $this->increment('amountpaid', $amount);
        $this->updateStatus();

        return $payment;
    }

    // === SCOPES ===

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for partially paid invoices
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['paid']);
    }

    /**
     * Scope by student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student', $studentId);
    }

    /**
     * Scope by course
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course', $courseId);
    }
}
