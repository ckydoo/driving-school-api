<?php
// app/Models/School.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'website',
        'start_time',
        'end_time',
        'operating_days',
        'invitation_code',
        'status',
    ];

    protected $casts = [
        'operating_days' => 'array',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {
            if (empty($school->slug)) {
                $school->slug = Str::slug($school->name);
                
                // Ensure slug is unique
                $originalSlug = $school->slug;
                $counter = 1;
                
                while (static::where('slug', $school->slug)->exists()) {
                    $school->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    /**
     * Get users belonging to this school
     */
    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

    /**
     * Get admin users for this school
     */
    public function admins()
    {
        return $this->hasMany(User::class, 'school_id')->where('role', 'admin');
    }

    /**
     * Get active schools
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}