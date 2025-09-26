<?php
// app/Models/SubscriptionInvoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'school_id',
        'subscription_package_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'total_amount',
        'billing_period',
        'status',
        'invoice_date',
        'due_date',
        'period_start',
        'period_end',
        'invoice_data',
        'stripe_invoice_id',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'invoice_data' => 'array'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function getTotalPaid(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getRemainingBalance(): float
    {
        return $this->total_amount - $this->getTotalPaid();
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'SUB-';
        $date = now()->format('Ym');
        $lastInvoice = self::where('invoice_number', 'like', $prefix . $date . '%')
                         ->orderBy('invoice_number', 'desc')
                         ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }
}

