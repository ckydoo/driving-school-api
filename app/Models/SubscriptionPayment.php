<?php

// app/Models/SubscriptionPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'school_id',
        'subscription_invoice_id',
        'payment_number',
        'amount',
        'payment_method',
        'status',
        'payment_date',
        'transaction_id',
        'reference_number',
        'gateway_response',
        'fee_amount',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'gateway_response' => 'array'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subscriptionInvoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getNetAmount(): float
    {
        return $this->amount - $this->fee_amount;
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY-';
        $date = now()->format('Ym');
        $lastPayment = self::where('payment_number', 'like', $prefix . $date . '%')
                          ->orderBy('payment_number', 'desc')
                          ->first();

        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->payment_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }
}