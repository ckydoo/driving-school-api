
<?php
// app/Models/Note.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_by',
        'note_for',
        'note'
    ];

    protected $casts = [
        'note_by' => 'integer',
        'note_for' => 'integer',
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'note_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'note_for');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('note_for', $userId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('note_by', $userId);
    }
}
