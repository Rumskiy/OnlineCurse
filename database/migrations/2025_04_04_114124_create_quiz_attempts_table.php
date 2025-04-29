<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            // ВИКОРИСТОВУЙТЕ foreignUuid ЗАМІСТЬ foreignId
            $table->foreignUuid('user_id') // Створює колонку типу UUID
            ->constrained('users')    // Посилається на 'id' в 'users'
            ->onDelete('cascade');
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->unsignedInteger('score');
            $table->unsignedInteger('total_questions');
            $table->float('percentage', 5, 2);
            $table->json('answers_details')->nullable();
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
