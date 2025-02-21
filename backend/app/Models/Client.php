<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

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