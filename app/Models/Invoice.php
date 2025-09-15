<?php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student',
        'course',
        'lessons',
        'price_per_lesson',
        'amountpaid',
        'due_date',
        'courseName',
        'status',
        'total_amount',
        'used_lessons',
        'invoice_number'
    ];

    protected $casts = [
        'student' => 'integer',
        'course' => 'integer',
        'lessons' => 'integer',
        'price_per_lesson' => 'decimal:2',
        'amountpaid' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'used_lessons' => 'integer',
        'due_date' => 'datetime',
    ];

    // Relationships
    public function studentUser()
    {
        return $this->belongsTo(User::class, 'student');
    }

    public function courseInfo()
    {
        return $this->belongsTo(Course::class, 'course');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoiceId');
    }



    // Accessors
    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->amountpaid;
    }

    public function getIsOverdueAttribute()
    {
        return $this->balance > 0 && $this->due_date->isPast();
    }

    public function getRemainingLessonsAttribute()
    {
        return $this->lessons - $this->used_lessons;
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
