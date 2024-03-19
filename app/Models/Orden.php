<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;
    protected $table = "ordenes";

    //relaciones entre los modelos
    public function user(){
        return $this->belongsTo(User::class);
    }
    //relacion de 1:N con DetalleOrden
    public function detalleOrdenes(){
        return $this->hasMany(DetalleOrden::class);
    }
}
