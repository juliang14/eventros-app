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
        Schema::table('ev_guests', function (Blueprint $table) {
            $table->boolean('confirmed')->default(false)->after('invite_code');
        });
    }

    public function down(): void
    {
        Schema::table('ev_guests', function (Blueprint $table) {
            $table->dropColumn('confirmed');
        });
    }
};
