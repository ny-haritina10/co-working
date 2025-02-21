<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Espace extends Model
{
    protected $table = 'espaces';
    
    protected $fillable = ['label', 'hour_price'];
}