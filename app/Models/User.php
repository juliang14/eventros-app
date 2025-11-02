<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'ev_users';

    protected $fillable = ['name','email','password'];

    protected $hidden = ['password','remember_token'];

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
