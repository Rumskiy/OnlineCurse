<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (!Schema::hasColumn('tests', 'total_time_limit')) {
                // Додаємо, якщо ще не існує (можливо, додано попередньою міграцією)
                $table->integer('total_time_limit')->nullable()->after('title');
            }
            if (Schema::hasColumn('tests', 'questions')) {
                $table->dropColumn('questions'); // Видаляємо JSON колонку
            }
            // Переконайся, що time_per_question існує або додай/видали за потребою
            if (!Schema::hasColumn('tests', 'time_per_question')) {
                $table->integer('time_per_question')->nullable()->default(30)->after('total_time_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (!Schema::hasColumn('tests', 'questions')) {
                $table->json('questions')->after('time_per_question'); // Повертаємо колонку
            }
            // Можливо, потрібно видалити total_time_limit при відкаті
            // $table->dropColumn('total_time_limit');
            // Можливо, потрібно повернути time_per_question, якщо видаляли
        });
    }
};
