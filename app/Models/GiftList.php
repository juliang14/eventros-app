<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftList extends Model
{
    protected $table = 'ev_gift_list';

    protected $fillable = ['event_id','item','is_reserved','guest_id'];

    public function event() { return $this->belongsTo(Event::class, 'event_id'); }
    public function reservedBy() { return $this->belongsTo(Guest::class, 'guest_id'); }
}
