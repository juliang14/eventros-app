<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name'); // Nombre del regalo
            $table->integer('quantity')->default(1); // Cantidad disponible
            $table->integer('reserved_count')->default(0); // Cuántos ya fueron reservados
            $table->boolean('is_required')->default(false); // Si es obligatorio
            $table->boolean('hide_when_reserved')->default(false); // Si debe ocultarse al reservarse todo
            $table->boolean('is_reserved')->default(false); // Si está completamente reservado
            $table->string('reserved_by')->nullable(); // IDs de invitados separados por "|"
            $table->string('image_path')->nullable(); // Imagen del regalo
            $table->timestamps();

            // Relaciones
            $table->foreign('event_id')->references('id')->on('ev_events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_gifts');
    }
};
