<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, 'taggable');
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, 'taggable');
    }

    public function tracks()
    {
        return $this->morphedByMany(Track::class, 'taggable');
    }
}
