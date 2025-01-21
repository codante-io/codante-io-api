<?php

namespace App\Http\Controllers;

use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(string $slug)
    {
        $lesson = Lesson::where('slug', $slug)->first();

        return new LessonResource($lesson);
    }

    public function setCompleted(Request $request, Lesson $lesson)
    {
        if (! $lesson) {
            abort(404);
        }
        $user = $request->user();

        $lesson->markAsCompleted($user);

        return response()->json([
            'message' => 'Lesson Completed',
            'result' => 'create',
            'lesson' => $lesson->id,
        ]);
    }

    public function setUncompleted(Request $request, Lesson $lesson)
    {
        if (! $lesson) {
            abort(404);
        }
        $user = $request->user();

        $lesson->markAsCompleted($user, false);

        return response()->json([
            'message' => 'Lesson Completed',
            'result' => 'destroy',
            'lesson' => $lesson->id,
        ]);
    }

    public function getUnusedSlug(Request $request)
    {
        $lessonName = $request->input('lesson_name');
        if (! $lessonName) {
            return response()->json(
                [
                    'error' => 'lesson_name is required',
                ],
                400
            );
        }

        $slug = Lesson::getUnusedSlug($lessonName);

        return response()->json([
            'slug' => $slug,
        ]);
    }
}
