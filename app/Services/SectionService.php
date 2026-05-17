<?php

namespace App\Services;

use App\Models\Section;
use App\Repositories\Contracts\SectionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SectionService
{
    protected $sectionRepository;

    public function __construct(SectionRepositoryInterface $sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * Створення розділу разом із файлами та відео
     */
    public function createSection(array $data, $sectionFile = null, $sectionVideo = null): Section
    {
        return DB::transaction(function () use ($data, $sectionFile, $sectionVideo) {
            $section = $this->sectionRepository->create($data);

            if ($sectionFile) {
                $section->addMedia($sectionFile)->toMediaCollection('section_files');
            }

            if ($sectionVideo) {
                $section->addMedia($sectionVideo)->toMediaCollection('section_videos');
            }

            return $section;
        });
    }

    /**
     * Оновлення розділу та керування прикріпленими медіа
     */
    public function updateSection(
        Section $section, 
        array $data, 
        $sectionFile = null, 
        $sectionVideo = null, 
        bool $removeSectionFile = false, 
        bool $removeSectionVideo = false
    ): Section {
        return DB::transaction(function () use ($section, $data, $sectionFile, $sectionVideo, $removeSectionFile, $removeSectionVideo) {
            $section = $this->sectionRepository->update($section, $data);

            // Обробка файлу домашнього завдання
            if ($sectionFile) {
                $section->clearMediaCollection('section_files');
                $section->addMedia($sectionFile)->toMediaCollection('section_files');
            } elseif ($removeSectionFile) {
                $section->clearMediaCollection('section_files');
            }

            // Обробка відео розділу
            if ($sectionVideo) {
                $section->clearMediaCollection('section_videos');
                $section->addMedia($sectionVideo)->toMediaCollection('section_videos');
            } elseif ($removeSectionVideo) {
                $section->clearMediaCollection('section_videos');
            }

            return $section->fresh();
        });
    }

    /**
     * Видалення розділу
     */
    public function deleteSection(Section $section): bool
    {
        return DB::transaction(function () use ($section) {
            $section->clearMediaCollection('section_files');
            $section->clearMediaCollection('section_videos');
            return $this->sectionRepository->delete($section);
        });
    }
}
