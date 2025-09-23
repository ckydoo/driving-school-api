<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRegistration extends Model
{
    protected $fillable = [
        'school_id',
        'user_id', 
        'device_id',
        'platform',
        'app_version',
        'status',
        'last_seen',
        'last_successful_sync',
        'requires_full_sync',
        'sync_metadata',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'last_successful_sync' => 'datetime',
        'requires_full_sync' => 'boolean',
        'sync_metadata' => 'array',
    ];

    // ===================================================================
    // RELATIONSHIPS
    // ===================================================================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeRecentlySeen($query, $hours = 24)
    {
        return $query->where('last_seen', '>=', now()->subHours($hours));
    }

    // ===================================================================
    // METHODS
    // ===================================================================

    /**
     * Update device's last seen timestamp
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen' => now()]);
    }

    /**
     * Mark device as requiring full sync
     */
    public function requireFullSync($reason = null)
    {
        $metadata = $this->sync_metadata ?? [];
        $metadata['full_sync_reason'] = $reason;
        $metadata['full_sync_required_at'] = now()->toISOString();

        $this->update([
            'requires_full_sync' => true,
            'sync_metadata' => $metadata,
        ]);
    }

    /**
     * Mark successful sync completion
     */
    public function markSyncCompleted($syncType = 'incremental', $stats = [])
    {
        $metadata = $this->sync_metadata ?? [];
        $metadata['last_sync_type'] = $syncType;
        $metadata['last_sync_stats'] = $stats;
        $metadata['last_sync_completed_at'] = now()->toISOString();

        $this->update([
            'last_successful_sync' => now(),
            'requires_full_sync' => false,
            'sync_metadata' => $metadata,
        ]);
    }

    /**
     * Check if device needs full sync
     */
    public function needsFullSync(): bool
    {
        // Explicitly marked for full sync
        if ($this->requires_full_sync) {
            return true;
        }

        // Never synced
        if (!$this->last_successful_sync) {
            return true;
        }

        // Haven't synced in over a week
        if ($this->last_successful_sync->diffInDays(now()) > 7) {
            return true;
        }

        return false;
    }

    /**
     * Get sync health status
     */
    public function getSyncHealthStatus(): array
    {
        $status = 'healthy';
        $issues = [];

        // Check last sync time
        if (!$this->last_successful_sync) {
            $status = 'warning';
            $issues[] = 'Never synced';
        } elseif ($this->last_successful_sync->diffInHours(now()) > 24) {
            $status = 'warning';
            $issues[] = 'Last sync over 24 hours ago';
        } elseif ($this->last_successful_sync->diffInDays(now()) > 7) {
            $status = 'critical';
            $issues[] = 'Last sync over 7 days ago';
        }

        // Check if device is active
        if ($this->status !== 'active') {
            $status = 'critical';
            $issues[] = 'Device is not active';
        }

        // Check if full sync is required
        if ($this->requires_full_sync) {
            $status = 'warning';
            $issues[] = 'Full sync required';
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'last_sync' => $this->last_successful_sync?->toISOString(),
            'last_seen' => $this->last_seen?->toISOString(),
        ];
    }
}
