<?php
// Update your Fleet model (app/Models/Fleet.php) with this complete version

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;

    protected $table = 'fleet';

    protected $fillable = [
        'carplate',
        'make',
        'model',
        'modelyear',
        'status',
        'school_id',    // ✅ Ensure this is included
        'instructor',
    ];

    protected $casts = [
        'instructor' => 'integer',
        'school_id' => 'integer',
        'modelyear' => 'integer',
    ];

    // === RELATIONSHIPS ===

    /**
     * Get the assigned instructor
     */
    public function assignedInstructor()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

    /**
     * Get the school this vehicle belongs to
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get schedules for this vehicle
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'car');
    }

    // === SCOPES ===

    /**
     * Scope for available vehicles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for vehicles in maintenance
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope for retired vehicles
     */
    public function scopeRetired($query)
    {
        return $query->where('status', 'retired');
    }

    /**
     * Scope for school-specific vehicles
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for current user's school (if not super admin)
     */
    public function scopeForCurrentUser($query, $user)
    {
        if (!$user->isSuperAdmin() && $user->school_id) {
            return $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhereNull('school_id'); // Temporary: include unassigned vehicles
            });
        }

        return $query;
    }

    // === ACCESSORS & MUTATORS ===

    /**
     * Get the full vehicle name
     */
    public function getFullVehicleNameAttribute()
    {
        return $this->make . ' ' . $this->model . ' (' . $this->carplate . ')';
    }

    /**
     * Get full name with year
     */
    public function getFullNameAttribute()
    {
        return $this->make . ' ' . $this->model . ' (' . $this->modelyear . ')';
    }

    /**
     * Accessor for license plate (for compatibility with schedules)
     */
    public function getLicensePlateAttribute()
    {
        return $this->carplate;
    }

    /**
     * Accessor for year (for compatibility)
     */
    public function getYearAttribute()
    {
        return $this->modelyear;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'available' => 'success',
            'maintenance' => 'warning',
            'retired' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Check if vehicle is available
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    // === BOOT METHOD ===

    /**
     * Boot method to handle automatic assignments
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            // Auto-assign school_id if not set
            if (!$vehicle->school_id) {
                $user = auth()->user();
                if ($user && $user->school_id && !$user->isSuperAdmin()) {
                    $vehicle->school_id = $user->school_id;
                }
            }
        });
    }
}
