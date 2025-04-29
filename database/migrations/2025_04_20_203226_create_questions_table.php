<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->string('type'); // 'single_choice', 'match', 'multiple_choice' etc.
            $table->text('text');
            $table->unsignedInteger('order')->default(0); // Порядок питання
            $table->unsignedInteger('points')->default(1); // Бали за питання
            $table->timestamps();

            $table->index('test_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
