<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_send_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('guest_id');
            $table->enum('channel', ['email', 'whatsapp', 'both'])->default('email');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            // Índices (mejor rendimiento en búsquedas por evento/guest)
            $table->index('event_id');
            $table->index('guest_id');
            $table->index('status');

            // Claves foráneas
            $table->foreign('event_id')
                  ->references('id')->on('ev_events')
                  ->onDelete('cascade');

            $table->foreign('guest_id')
                  ->references('id')->on('ev_guests')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_send_invitations');
    }
};
