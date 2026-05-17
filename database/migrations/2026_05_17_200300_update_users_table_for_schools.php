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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('school_id')->nullable()->after('id')->constrained('schools')->onDelete('set null');
            $table->foreignId('school_class_id')->nullable()->after('school_id')->constrained('school_classes')->onDelete('set null');
            
            // Змінюємо enum на string для гнучкості з ролями: student, teacher, school_admin, parent, super_admin
            $table->string('role')->default('student')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['users_school_id_foreign']);
            $table->dropForeign(['users_school_class_id_foreign']);
            $table->dropColumn(['school_id', 'school_class_id']);
            
            // Повертаємо enum назад у разі відкату
            $table->enum('role', ['1', '2', '3'])->default('1')->change();
        });
    }
};
