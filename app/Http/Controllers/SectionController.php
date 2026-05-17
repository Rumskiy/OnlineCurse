<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\CreateSection\CreateSectionRequest;
use App\Http\Requests\Section\UpdateSection\UpdateSectionRequest;
use App\Http\Resources\Section\SectionResource;
use App\Models\Section;
use App\Services\SectionService;
use App\Repositories\Contracts\SectionRepositoryInterface;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    protected $sectionService;
    protected $sectionRepository;

    public function __construct(SectionService $sectionService, SectionRepositoryInterface $sectionRepository)
    {
        $this->sectionService = $sectionService;
        $this->sectionRepository = $sectionRepository;
    }

    public function index($courseId)
    {
        $sections = $this->sectionRepository->getByCourseId($courseId);
        return response()->json(SectionResource::collection($sections));
    }

    public function store(CreateSectionRequest $request)
    {
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'contentSection' => $request->contentSection,
            'course_id' => $request->course_id,
        ];

        $sectionFile = $request->file('section_file');
        $sectionVideo = $request->file('section_video');

        $section = $this->sectionService->createSection($data, $sectionFile, $sectionVideo);

        return $this->sendJsonWithData($section, SectionResource::class);
    }

    public function show($id)
    {
        $section = $this->sectionRepository->findById($id);

        if (!$section) {
            return response()->json(['message' => 'Розділ не знайдено'], 404);
        }

        return response()->json($section);
    }

    public function update(UpdateSectionRequest $request, Section $section)
    {
        $validatedData = $request->validated();

        $data = [
            'title' => $validatedData['title'] ?? $section->title,
            'description' => $validatedData['description'] ?? $section->description,
            'contentSection' => $validatedData['contentSection'] ?? $section->contentSection,
        ];

        $sectionFile = $request->file('section_file');
        $sectionVideo = $request->file('section_video');
        $removeSectionFile = $request->boolean('remove_section_file');
        $removeSectionVideo = $request->boolean('remove_section_video');

        $updatedSection = $this->sectionService->updateSection(
            $section, 
            $data, 
            $sectionFile, 
            $sectionVideo, 
            $removeSectionFile, 
            $removeSectionVideo
        );

        return $this->sendJsonWithData($updatedSection, SectionResource::class);
    }

    public function destroy(Section $section)
    {
        $this->sectionService->deleteSection($section);
        return response()->json(['message' => 'Section deleted']);
    }
}
