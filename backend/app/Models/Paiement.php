<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['id_reservation', 'reference', 'date_paiement', 'validated_at'];

    protected $casts = [
        'date_paiement' => 'date',
        'validated_at' => 'datetime' 
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'id_reservation');
    }
}