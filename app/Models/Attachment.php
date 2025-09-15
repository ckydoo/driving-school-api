
<?php
// app/Models/Attachment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'path',
        'uploaded_by',
        'entity_type',
        'entity_id'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_by' => 'integer',
        'entity_id' => 'integer',
    ];

    // Relationships
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Polymorphic relationship helper
    public function entity()
    {
        switch ($this->entity_type) {
            case 'user':
                return $this->belongsTo(User::class, 'entity_id');
            case 'course':
                return $this->belongsTo(Course::class, 'entity_id');
            case 'schedule':
                return $this->belongsTo(Schedule::class, 'entity_id');
            default:
                return null;
        }
    }

    // Scopes
    public function scopeForEntity($query, $type, $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }

    // Accessors
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
