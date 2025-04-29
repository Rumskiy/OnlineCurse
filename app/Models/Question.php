<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia; // Імпорт
use Spatie\MediaLibrary\InteractsWithMedia; // Імпорт

// Додаємо HasMedia
class Question extends Model implements HasMedia
{
    // Додаємо трейт
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'test_id',
        'type',
        'text',
        'order',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
        'order' => 'integer',
    ];

    // Додаємо image_url до $appends, щоб він автоматично додавався до JSON/масиву
    protected $appends = ['image_url'];

    // Зв'язок: Питання належить Тесту
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    // Зв'язок: Питання має багато Варіантів
    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order'); // Сортуємо варіанти
    }

    // Зв'язок: Питання має багато Пар для з'єднання
    public function matchPairs(): HasMany
    {
        return $this->hasMany(MatchPair::class)->orderBy('order'); // Сортуємо пари
    }

    // Реєстрація медіа-колекції для зображення питання
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_image')
            ->singleFile(); // Дозволяємо тільки один файл у цій колекції
    }

    // Аксессор для отримання URL зображення
    public function getImageUrlAttribute(): ?string
    {
        // Повертає URL першого медіафайлу в колекції 'question_image'
        // або null, якщо зображення немає.
        return $this->getFirstMediaUrl('question_image');
    }
}
