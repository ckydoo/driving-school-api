<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'student_id', 'course_id', 'lessons',
        'price_per_lesson', 'total_amount', 'amount_paid',
        'due_date', 'status', 'notes'
    ];

    protected $casts = [
        'lessons' => 'integer',
        'price_per_lesson' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function getIsOverdueAttribute()
    {
        return $this->balance > 0 && $this->due_date->isPast();
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
    }
}
