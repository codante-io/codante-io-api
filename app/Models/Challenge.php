<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function workshop()
    {
        return $this->hasOne(Workshop::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function tracks()
    {
        return $this->morphToMany(Track::class, 'trackable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
