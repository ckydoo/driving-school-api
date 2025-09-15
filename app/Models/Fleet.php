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
        'make', 'model', 'registration', 'year', 'transmission',
        'status', 'assigned_instructor_id', 'insurance_expiry',
        'mot_expiry', 'mileage', 'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'insurance_expiry' => 'date',
        'mot_expiry' => 'date',
    ];

    // Relationships
    public function assignedInstructor()
    {
        return $this->belongsTo(User::class, 'assigned_instructor_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'car_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeManual($query)
    {
        return $query->where('transmission', 'manual');
    }

    public function scopeAutomatic($query)
    {
        return $query->where('transmission', 'automatic');
    }
}
