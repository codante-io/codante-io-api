<?php

namespace App\Models;

use App\Traits\Commentable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;
    use Commentable;

    protected $guarded = ["id"];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(["completed_at"]);
    }

    // Gets the Vimeo ID from the video URL
    protected function vimeoId(): Attribute
    {
        return Attribute::make(
            get: fn() => substr(
                $this->video_url,
                strrpos($this->video_url, "/") + 1
            )
        );
    }

    public function userCompleted(User $user, bool $setComplete = true)
    {
        if (!$setComplete) {
            $this->users()->detach($user->id);
            event(new \App\Events\UserErasedLesson($user, $this->workshop));
            return;
        }
        $this->users()->syncWithoutDetaching([
            $user->id => [
                "completed_at" => now(),
            ],
        ]);

        event(new \App\Events\UserCompletedLesson($user, $this->workshop));
    }
}
