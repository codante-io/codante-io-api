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
            ->with("workshop.instructor")
            ->get();

        return $workshops
            ->concat($challenges)
            ->sortBy(function ($trackable) {
                return $trackable->pivot->position;
            })
            ->flatten()
            ->groupBy("pivot.section_id");
    }

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, "trackable")->withPivot(
            "position",
            "name",
            "description",
            "section_id"
        );
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, "trackable")->withPivot(
            "position",
            "name",
            "description",
            "section_id"
        );
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function trackSections()
    {
        return $this->hasMany(TrackSection::class);
    }

    public function sectionsWithTrackables()
    {
        $sections = $this->trackSections()->with("tags");

        $trackables = $this->trackables();

        return $sections->get()->map(function ($section) use ($trackables) {
            $section->trackables = $trackables->get($section->id);

            // $section->tags = $section->trackables
            //     ->map(function ($trackable) {
            //         return $trackable->tags;
            //     })
            //     ->flatten()
            //     ->unique("name")
            //     ->values();

            $section->instructors = $section->trackables
                ->map(function ($trackable) {
                    return $trackable->instructor;
                })
                ->filter()
                ->flatten()
                ->unique("name")
                ->values();

            return $section;
        });
    }
}
