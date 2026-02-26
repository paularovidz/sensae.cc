<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['map_title', 'map_subtitle']);
            $table->string('map_city')->nullable()->after('map_longitude');
            $table->unsignedSmallInteger('map_travel_time')->nullable()->after('map_city');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['map_city', 'map_travel_time']);
            $table->string('map_title')->nullable()->after('map_longitude');
            $table->string('map_subtitle')->nullable()->after('map_title');
        });
    }
};
