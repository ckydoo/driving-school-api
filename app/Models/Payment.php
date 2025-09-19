<?php
// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoiceId',
        'amount',
        'method',
        'status',
        'paymentDate',
        'notes',
        'reference',
        'receipt_path',
        'receipt_generated_at',
        'cloud_storage_path',
        'receipt_file_size',
        'receipt_type',
        'receipt_generated',
        'userId'
    ];

    protected $casts = [
        'invoiceId' => 'integer',
        'amount' => 'decimal:2',
        'paymentDate' => 'datetime',
        'receipt_generated' => 'boolean',
        'receipt_file_size' => 'integer',
        'userId' => 'integer',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }

    public function scopeWithReceipt($query)
    {
        return $query->where('receipt_generated', true);
    }
}
