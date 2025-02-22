<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'client'; 

    protected $fillable = [
        'name_client',
        'numero_client',
    ]; 

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_client');
    }
}