<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->date('date');

            $table->string('status')->default('belum_absen'); 
            // contoh: hadir, izin, sakit, alfa, belum_absen

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->string('note')->nullable();

            $table->timestamps();

            // biar 1 mahasiswa cuma punya 1 record per tanggal
            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
