<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{

    public function setCompleted(Request $request, Lesson $lesson)
    {
        if (!$lesson) {
            abort(404);
        }
        $user = $request->user();

        $lesson->userCompleted($user);

        return response()->json([
            "message" => "Lesson Completed",
            "result" => "create",
            "lesson" => $lesson->id,
        ]);
    }

    public function setUncompleted(Request $request, Lesson $lesson)
    {
        if (!$lesson) {
            abort(404);
        }
        $user = $request->user();

        $lesson->userCompleted($user, false);
        return response()->json([
            "message" => "Lesson Completed",
            "result" => "destroy",
            "lesson" => $lesson->id,
        ]);
    }

    public function getLessonThumbnail(Lesson $lesson)
    {
        if (!$lesson) {
            abort(404);
        }

        $thumbnail = $lesson->getThumbnail();

        return response()->json([
            "message" => "Lesson Thumbnail",
            "thumbnail" => $thumbnail,
        ]);
    }
}
