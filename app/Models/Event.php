<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'ev_events';
    protected $casts = [
        'event_date' => 'datetime',
    ];

    protected $fillable = [
        'title',
        'event_date',
        'location',
        'description',
        'user_id',
    ];
}
