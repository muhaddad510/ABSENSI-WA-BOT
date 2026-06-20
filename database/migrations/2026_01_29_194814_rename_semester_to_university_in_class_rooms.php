<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropColumn('semester');
        });

        Schema::table('class_rooms', function (Blueprint $table) {
            $table->string('university')->nullable()->after('code');
        });
    }

    public function down()
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropColumn('university');
        });

        Schema::table('class_rooms', function (Blueprint $table) {
            $table->string('semester')->nullable();
        });
    }

};
