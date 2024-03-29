<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ["id"];

    public function trackables()
    {
        $workshops = $this->workshops()
            ->with("lessons")
            ->with("instructor")
            ->get();
        $challenges = $this->challenges()
            ->withCount("users")
            ->with("tags")
            ->get();

        return $workshops
            ->concat($challenges)
            ->sortBy(function ($trackable) {
                return $trackable->pivot->position;
            })
            ->flatten();
    }

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, "trackable")->withPivot(
            "position"
        );
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, "trackable")->withPivot(
            "position"
        );
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }
}
