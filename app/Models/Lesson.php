<?php

namespace App\Models;

use App\Traits\Commentable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use Commentable;
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function lessonable()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['completed_at']);
    }

    // Gets the Vimeo ID from the video URL
    protected function vimeoId(): Attribute
    {
        return Attribute::make(
            get: fn () => substr(
                $this->video_url,
                strrpos($this->video_url, '/') + 1
            )
        );
    }

    public function userCompleted(?string $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->users()
            ->where('user_id', $userId)
            ->exists();
    }

    public function markAsCompleted(User $user, bool $setComplete = true)
    {
        if (! $setComplete) {
            $this->users()->detach($user->id);
            event(new \App\Events\UserErasedLesson($user));

            return;
        }
        $this->users()->syncWithoutDetaching([
            $user->id => [
                'completed_at' => now(),
            ],
        ]);

        event(new \App\Events\UserCompletedLesson($user));
    }

    public static function getUnusedSlug(string $lessonName): string
    {
        $slug = Str::slug($lessonName);
        $count = 0;

        do {
            $newSlug = $slug;
            if ($count > 0) {
                $newSlug .= '-'.$count;
            }

            $count++;
        } while (Lesson::where('slug', $newSlug)->exists());

        return $newSlug;
    }
}
