<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('role_id')->default(2); // relación a roles
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('ev_roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ev_users');
    }
};
