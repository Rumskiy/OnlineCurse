<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'title_img',
        'description',
        'category_id',
        'author_id'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('title_images')
            ->singleFile();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
}

