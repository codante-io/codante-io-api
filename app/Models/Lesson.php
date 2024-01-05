<?php

namespace App\Models;

use App\Services\VimeoThumbnail;
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
            return;
        }
        $this->users()->syncWithoutDetaching([
            $user->id => [
                "completed_at" => now(),
            ],
        ]);
    }
}
