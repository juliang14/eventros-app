<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $table = 'ev_guests';

    /**
     * Campos que se pueden asignar en masa.
     */
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'invite_code',
        'companions_names',
        'companions_count',
        'confirmed'
    ];

    /**
     * Relaciones
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function rsvp()
    {
        return $this->hasOne(Rsvp::class, 'guest_id');
    }

    public function reservedGifts()
    {
        return $this->hasMany(Gift::class, 'guest_id');
    }

    /**
     * Eventos del modelo
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->invite_code)) {
                $model->invite_code = Str::random(12);
            }

            // Calcular el número de acompañantes automáticamente si existe la lista
            if (!empty($model->companions_names)) {
                $model->companions_count = count(explode('|', $model->companions_names));
            }
        });

        static::updating(function ($model) {
            if (!empty($model->companions_names)) {
                $model->companions_count = count(explode('|', $model->companions_names));
            } else {
                $model->companions_count = 0;
            }
        });
    }
}
