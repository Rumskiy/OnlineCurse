<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Test title
            $table->json('questions'); // Array of questions with their options
            $table->integer('time_per_question')->nullable()->default(30);
            $table->foreignId('section_id')
            ->unique()
            ->constrained('sections')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};

