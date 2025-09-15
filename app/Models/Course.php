<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'course');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'course');
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
