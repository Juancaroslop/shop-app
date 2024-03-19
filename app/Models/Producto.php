<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    //definiendo relacion inversa con marca
    public function marca(){
        return $this->belongsTo(Marca::class);
    }
    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }

    //relacion 1:N con Imagen
    public function imagenes(){
        return $this->hasMany(Imagen::class);
    }

    //relacion 1:N con DetalleOrden
    public function detalleOrdenes(){
        return $this->hasMany(DetalleOrden::class);
    }
}
