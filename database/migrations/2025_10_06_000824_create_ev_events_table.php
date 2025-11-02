<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('event_date');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('user_id'); // creador del evento
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ev_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_events');
    }
};
