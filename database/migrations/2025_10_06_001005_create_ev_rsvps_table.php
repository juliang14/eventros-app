<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_rsvps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_id');
            $table->enum('status', ['confirmed', 'declined', 'pending'])->default('pending');
            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('ev_guests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_rsvps');
    }
};
