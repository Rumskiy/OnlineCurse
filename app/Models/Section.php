<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Section extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'section_img',
        'content',
        'course_id',
        'order'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('section_images')
            ->singleFile();

        $this->addMediaCollection('section_videos')
            ->useDisk('s3');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function test()
    {
        return $this->hasOne(Test::class);
    }
}

