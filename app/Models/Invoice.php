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

    // ✅ Add this relationship - this is what was missing!
    public function student()
    {
        return $this->belongsTo(User::class, 'student');
    }

    // ✅ Add course relationship if not already present
    public function course()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    // ✅ Add payments relationship if not already present
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoiceId');
    }

    // Helper methods
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->amountpaid;
    }

    public function getStatusAttribute($value)
    {
        return $value ?: 'unpaid';
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }

    // Automatically update status based on payment amount
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
    }
}