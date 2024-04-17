<?php

namespace App\Models;

use App\Http\Resources\WorkshopResource;
use App\Http\Resources\WorkshopTrackResource;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->with("tags")
            ->with("instructor")
            ->get();

        $challenges = $this->challenges()
            ->withCount("users")
            ->with("tags")
            ->with("workshop.instructor")
            ->get();

        $items = $this->items()
            ->with("tags")
            ->get();

        $trackables = $workshops->concat($challenges)->concat($items);

        $trackableIds = $trackables->pluck("pivot.id");

        $userTrackables = Auth::user()
            ? DB::table("trackable_user")
                ->whereIn("trackable_id", $trackableIds)
                ->where("user_id", Auth::user()->id)
                ->where("completed", true)
                ->get()
            : new Collection();

        $trackables = $trackables->map(function ($trackable) use (
            $userTrackables
        ) {
            $trackable->completed =
                (bool) $userTrackables
                    ->where("trackable_id", $trackable->pivot->id)
                    ->first()?->completed ?? false;

            return $trackable;
        });

        return $trackables
            ->sortBy(function ($trackable) {
                return $trackable->pivot->position;
            })
            ->groupBy("pivot.section_id");
    }

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, "trackable")->withPivot(
            "id",
            "position",
            "name",
            "description",
            "section_id"
        );
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, "trackable")->withPivot(
            "id",
            "position",
            "name",
            "description",
            "section_id"
        );
    }

    public function items()
    {
        return $this->morphedByMany(TrackItem::class, "trackable")->withPivot(
            "id",
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
