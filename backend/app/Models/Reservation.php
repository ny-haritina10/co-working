<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'id_client',
        'id_espace',
        'reference',
        'datetime_reservation',
        'hour_duration'
    ];

    protected $casts = [
        'datetime_reservation' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function espace()
    {
        return $this->belongsTo(Espace::class, 'id_espace');
    }

    public function options()
    {
        return $this->belongsToMany(Option::class, 'reservation_options', 'id_reservation', 'id_option');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_reservation');
    }
}

class ReservationOption extends Model
{
    protected $fillable = ['id_reservation', 'id_option'];
    public $timestamps = false;
}