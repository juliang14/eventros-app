<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'ev_events';

    protected $fillable = [
        'title',
        'date',
        'location',
        'description',
    ];
}
