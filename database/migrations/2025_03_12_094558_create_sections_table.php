<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable(); // Текст або TipTap JSON
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(1); // Порядок розділу в курсі
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
