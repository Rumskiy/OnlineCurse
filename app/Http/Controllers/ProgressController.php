<?php

namespace App\Http\Controllers;

use App\Models\SectionProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function markAsCompleted(Request $request, $sectionId)
    {
        $userId = Auth::id();

        $progress = SectionProgress::updateOrCreate(
            ['user_id' => $userId, 'section_id' => $sectionId],
            [
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        return $this->sendJsonWithData($progress, 'Section marked as completed');
    }

    public function getProgress($courseId)
    {
        $userId = Auth::id();
        
        $progress = SectionProgress::where('user_id', $userId)
            ->whereHas('section', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->get();

        return $this->sendJsonWithData($progress);
    }
    
    public function getUserStats()
    {
        $userId = Auth::id();
        
        $completedSectionsCount = SectionProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();
            
        // You can add more stats here later
        
        return $this->sendJsonWithData([
            'completed_sections_count' => $completedSectionsCount,
        ]);
    }
}
