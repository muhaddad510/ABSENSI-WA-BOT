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
        Schema::table('students', function (Blueprint $table) {

            // cek dulu biar gak error
            if (Schema::hasColumn('students', 'semester')) {
                $table->dropColumn('semester');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            // kalau rollback — balikin lagi
            if (!Schema::hasColumn('students', 'semester')) {
                $table->string('semester')->nullable()->after('nim');
            }

        });
    }
};
