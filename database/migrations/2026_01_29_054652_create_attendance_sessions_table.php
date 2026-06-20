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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('class_room_id');
            $table->date('date');

            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('is_closed')->default(false);
            $table->boolean('reminder_sent')->default(false);

            $table->timestamps();

            $table->unique(['teacher_id', 'class_room_id', 'date']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
