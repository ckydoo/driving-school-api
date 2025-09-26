<?php
// app/Models/SubscriptionHistory.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    // FIXED: Specify correct table name
    protected $table = 'subscription_history';
    
    protected $fillable = [
        'school_id',
        'action',
        'old_data',
        'new_data',
        'reason',
        'performed_by'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Helper methods
    public static function logChange(int $schoolId, string $action, array $oldData = null, array $newData = null, string $reason = null, int $performedBy = null): void
    {
        self::create([
            'school_id' => $schoolId,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'reason' => $reason,
            'performed_by' => $performedBy ?? auth()->id()
        ]);
    }

    // Scope for recent changes
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Scope for specific actions
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // Get formatted action description
    public function getFormattedActionAttribute(): string
    {
        $descriptions = [
            'created' => 'School subscription created',
            'upgraded' => 'Package upgraded',
            'downgraded' => 'Package downgraded',
            'cancelled' => 'Subscription cancelled',
            'reactivated' => 'Subscription reactivated',
            'trial_started' => 'Trial period started',
            'trial_extended' => 'Trial period extended',
            'package_changed' => 'Package changed',
            'subscription_extended' => 'Subscription period extended',
            'payment_received' => 'Payment received',
            'invoice_generated' => 'Invoice generated'
        ];

        return $descriptions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}