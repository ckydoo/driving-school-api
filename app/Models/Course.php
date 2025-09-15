<?php
// app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'lessons', 'type',
        'status', 'requirements', 'duration_minutes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requirements' => 'array',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePractical($query)
    {
        return $query->where('type', 'practical');
    }

    public function scopeTheory($query)
    {
        return $query->where('type', 'theory');
    }
}
