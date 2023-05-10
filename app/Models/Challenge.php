<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'published_at' => 'date',
    ];

    public function getTypeAttribute()
    {
        return 'challenge';
    }

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
        return $this->belongsToMany(User::class)->withPivot(['completed', 'fork_url', 'joined_discord']);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
