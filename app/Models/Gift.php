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

    // Relación con evento
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // Devuelve los IDs de invitados que ya reservaron (como array)
    public function getReservedByListAttribute()
    {
        return $this->reserved_by ? explode('|', $this->reserved_by) : [];
    }

    // Verifica si el regalo está completamente reservado
    public function getIsFullyReservedAttribute()
    {
        return $this->reserved_count >= $this->quantity;
    }

    // Retorna la imagen (default si no tiene)
    public function getImageUrlAttribute()
    {
        return $this->image_path ?: asset('images/default_gift.png');
    }

    public function isFullyReserved(): bool
    {
        // Si el regalo es obligatorio, nunca se considera "agotado"
        if ($this->is_required) {
            return false;
        }

        // Si tiene cantidad limitada, se considera agotado si reserved_count >= quantity
        return $this->reserved_count >= $this->quantity;
    }

}
