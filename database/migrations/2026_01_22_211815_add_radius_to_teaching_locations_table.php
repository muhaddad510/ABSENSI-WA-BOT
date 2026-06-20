<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teaching_locations', function (Blueprint $table) {
            $table->integer('radius_m')->default(200)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('teaching_locations', function (Blueprint $table) {
            $table->dropColumn('radius_m');
        });
    }
};
