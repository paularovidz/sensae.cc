<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject');
            $table->integer('word_count');
            $table->string('tone');
            $table->text('context')->nullable();
            $table->string('model');
            $table->integer('prompt_tokens');
            $table->integer('completion_tokens');
            $table->integer('total_tokens');
            $table->decimal('cost_estimate', 8, 6);
            $table->foreignUuid('article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::table('settings')->insertOrIgnore([
            'key' => 'ai_default_tone',
            'value' => 'nous',
            'type' => 'string',
            'group' => 'ai',
            'label' => 'Tonalité par défaut',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        DB::table('settings')->where('key', 'ai_default_tone')->delete();
    }
};
