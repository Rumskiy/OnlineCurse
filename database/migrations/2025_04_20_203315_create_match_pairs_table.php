<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('left_text');
            $table->string('right_text'); // Правильна відповідь
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();

            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_pairs');
    }
};
