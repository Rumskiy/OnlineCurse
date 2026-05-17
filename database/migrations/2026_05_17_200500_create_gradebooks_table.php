<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gradebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            $table->foreignId('test_id')->nullable()->constrained('tests')->onDelete('cascade');
            $table->integer('grade'); // Оцінка (наприклад, 1-12)
            $table->text('teacher_comment')->nullable();
            $table->foreignUuid('graded_by')->constrained('users')->onDelete('cascade'); // Вчитель, який поставив оцінку
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gradebooks');
    }
};
