<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7)->default(-6.2000000);
            $table->decimal('longitude', 10, 7)->default(106.8166660);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_locations');
    }
};
