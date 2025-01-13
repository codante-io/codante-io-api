<?php

namespace App\Models;

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

    protected $guarded = ['id'];

    // Retorna Trackables (workshops e mini projetos)
    /**
     * Get all trackable items (workshops and challenges) for this track
     */
    public function trackables(): Collection
    {
<<<<<<< HEAD
        $allTrackables = $this->workshops
            ->concat($this->challenges()->get())
            ->sortBy('pivot.position');

        if (! Auth::check()) {
            return $allTrackables->map(function ($trackable) {
                $trackable->track_slug = $this->slug;

                return $trackable;
            });
        }
=======
        $workshops = $this->workshops()
            ->with('lessons')
            ->with('tags')
            ->with('instructor')
            ->get();

        $challenges = $this->challenges()
            ->withCount('users')
            ->with('tags')
            ->with('workshop.instructor')
            ->get();

        $items = $this->items()
            ->with('tags')
            ->get();
>>>>>>> main

        $trackableIds = $allTrackables->pluck('pivot.id');
        $completedTrackables = $this->getUserCompletedTrackables($trackableIds);

<<<<<<< HEAD
        return $allTrackables->map(function ($trackable) use ($completedTrackables) {
            $trackable->completed = $completedTrackables
                ->contains('trackable_id', $trackable->pivot->id);
            $trackable->track_slug = $this->slug;
=======
        $trackableIds = $trackables->pluck('pivot.id');

        $userTrackables = Auth::user()
            ? DB::table('trackable_user')
                ->whereIn('trackable_id', $trackableIds)
                ->where('user_id', Auth::user()->id)
                ->where('completed', true)
                ->get()
            : new Collection();

        $trackables = $trackables->map(function ($trackable) use (
            $userTrackables
        ) {
            $trackable->completed =
                (bool) $userTrackables
                    ->where('trackable_id', $trackable->pivot->id)
                    ->first()?->completed ?? false;
>>>>>>> main

            return $trackable;
        });
    }

<<<<<<< HEAD
    private function getUserCompletedTrackables($trackableIds)
    {
        if (! Auth::check()) {
            return new Collection;
        }

        return DB::table('trackable_user')
            ->whereIn('trackable_id', $trackableIds)
            ->where('user_id', Auth::user()->id)
            ->where('completed', true)
            ->get();
=======
        return $trackables
            ->sortBy(function ($trackable) {
                return $trackable->pivot->position;
            })
            ->groupBy('pivot.section_id');
>>>>>>> main
    }

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, 'trackable')->withPivot(
            'id',
            'position',
            'name',
            'description',
            'section_id'
        );
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, 'trackable')->withPivot(
            'id',
            'position',
            'name',
            'description',
            'section_id'
        );
    }

    public function items()
    {
        return $this->morphedByMany(TrackItem::class, 'trackable')->withPivot(
            'id',
            'position',
            'name',
            'description',
            'section_id'
        );
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function trackSections()
    {
        return $this->hasMany(TrackSection::class);
    }

<<<<<<< HEAD
    // public function sectionsWithTrackables()
    // {
    //     $sections = $this->trackSections()->with("tags");
=======
    public function sectionsWithTrackables()
    {
        $sections = $this->trackSections()->with('tags');
>>>>>>> main

    //     $trackables = $this->trackables();

    //     return $sections->get()->map(function ($section) use ($trackables) {
    //         $section->trackables = $trackables->get($section->id);

<<<<<<< HEAD
    //         $section->instructors = $section->trackables
    //             ->map(function ($trackable) {
    //                 return $trackable->instructor;
    //             })
    //             ->filter()
    //             ->flatten()
    //             ->unique("name")
    //             ->values();
=======
            $section->instructors = $section->trackables
                ->map(function ($trackable) {
                    return $trackable->instructor;
                })
                ->filter()
                ->flatten()
                ->unique('name')
                ->values();
>>>>>>> main

    //         return $section;
    //     });
    // }
}
