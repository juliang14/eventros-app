<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ev_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, user
            $table->timestamps();
        });

        // agrega el campo role_id en ev_users
        Schema::table('ev_users', function (Blueprint $table) {
            $table->foreignId('role_id')
                  ->after('id')
                  ->default(2)
                  ->constrained('ev_roles')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ev_users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('ev_roles');
    }
};
