<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->foreignId('class_room_id')
            ->nullable()
            ->after('teacher_id')
            ->constrained('class_rooms')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropForeign(['class_room_id']);
        $table->dropColumn('class_room_id');
    });
}

};
