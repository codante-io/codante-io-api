<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ["id"];

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, "taggable");
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, "taggable");
    }

    public function tracks()
    {
        return $this->morphedByMany(Track::class, "taggable");
    }

    public function trackSections()
    {
        return $this->morphedByMany(TrackSection::class, "taggable");
    }
}
