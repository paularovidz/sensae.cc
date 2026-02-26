<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->decimal('map_latitude', 10, 7)->nullable()->after('image');
            $table->decimal('map_longitude', 10, 7)->nullable()->after('map_latitude');
            $table->string('map_title')->nullable()->after('map_longitude');
            $table->string('map_subtitle')->nullable()->after('map_title');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['map_latitude', 'map_longitude', 'map_title', 'map_subtitle']);
        });
    }
};
