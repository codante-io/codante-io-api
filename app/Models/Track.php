<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, 'trackable');
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, 'trackable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
