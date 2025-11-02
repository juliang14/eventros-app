<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_gift_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('item');
            $table->boolean('is_reserved')->default(false);
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('ev_events')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('ev_guests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_gift_list');
    }
};
