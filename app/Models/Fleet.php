<?php
// app/Models/Fleet.php - CORRECTED VERSION
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;

    protected $table = 'fleet';

    // ✅ FIXED: Added missing 'status' field to fillable array
    protected $fillable = [
        'carplate',
        'make',
        'model',
        'modelyear',
        'status',        // ✅ This was missing!
        'school_id',
        'instructor'
    ];

    protected $casts = [
        'instructor' => 'integer',
    ];

    // Relationships
    public function assignedInstructor()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'vehicle');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->whereHas('assignedInstructor', function($q) {
            $q->where('status', 'Active');
        });
    }

    // Accessors
    public function getFullVehicleNameAttribute()
    {
        return $this->make . ' ' . $this->model . ' (' . $this->carplate . ')';
    }
}