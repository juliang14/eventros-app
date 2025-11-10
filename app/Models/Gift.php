<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $table = 'ev_gifts';

    protected $fillable = [
        'event_id',
        'name',
        'quantity',
        'reserved_count',
        'is_required',
        'hide_when_reserved',
        'is_reserved',
        'reserved_by',
        'image_path',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function getReservedByListAttribute()
    {
        return $this->reserved_by ? explode('|', $this->reserved_by) : [];
    }

    public function getIsFullyReservedAttribute()
    {
        return $this->reserved_count >= $this->quantity;
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset(ltrim($this->image_path, '/')) : asset('images/default_gift.png');
    }

    public function isFullyReserved(): bool
    {
        if ($this->is_required) return false;
        return $this->reserved_count >= $this->quantity;
    }
}
