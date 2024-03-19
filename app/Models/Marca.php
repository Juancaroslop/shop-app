<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    //Relacion de 1:N con productos
    public function productos(){
        return $this->hasMany(Producto::class);
    }
    
}
