<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackItem extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function tracks(): MorphToMany
    {
        return $this->morphToMany(Track::class, 'trackable');
    }
}
