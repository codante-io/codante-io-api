<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, "replying_to");
    }

    public function reactions()
    {
        return $this->morphMany(
            Reaction::class,
            "reactable",
            "reactable_type",
            "reactable_id"
        );
    }
}
