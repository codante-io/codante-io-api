<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function trackables()
    {
        $workshops = $this->workshops;
        $challenges = $this->challenges;

        return $workshops->concat($challenges)->sortBy(function ($trackable) {
            return $trackable->pivot->position;
        })->flatten();
    }

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, 'trackable')->withPivot('position');
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, 'trackable')->withPivot('position');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
