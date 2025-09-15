<?php
// app/Models/Currency.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol'
    ];

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->code . ')';
    }
}
