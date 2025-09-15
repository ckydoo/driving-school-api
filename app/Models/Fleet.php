<?php
// app/Models/Fleet.php
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
