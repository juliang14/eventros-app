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
            $table->string('name');
            $table->boolean('is_reserved')->default(false);
            $table->unsignedBigInteger('reserved_by')->nullable(); // guest_id si alguien lo separa
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('ev_events')->onDelete('cascade');
            $table->foreign('reserved_by')->references('id')->on('ev_guests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_gifts');
    }
};
