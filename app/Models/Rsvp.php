<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rsvp extends Model
{
    protected $table = 'ev_rsvp';

    protected $fillable = ['guest_id','status','companions'];

    public function guest() { return $this->belongsTo(Guest::class, 'guest_id'); }
}
