

// =====================================================

<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'title',
        'message',
        'type',
        'read',
        'status'
    ];

    protected $casts = [
        'user' => 'integer',
        'read' => 'boolean',
    ];

    // Relationships
    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user', $userId);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'Sent');
    }
}
