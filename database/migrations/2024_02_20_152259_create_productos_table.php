<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('descripcion', 150);
            $table->decimal('precio', 8, 2);
            $table->decimal('stock', 8, 2);
            $table->string('estado', 1) -> default('D');
            $table->string('modelo', 80) -> nullable();
            //Definiendo la columna marca_id para la fk
            $table->unsignedInteger('marca_id');
            $table->foreign('marca_id')->references('id')->on('marcas');
            $table->unsignedInteger('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
