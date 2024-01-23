<?php

namespace App\Models;

use App\Http\Resources\CommentResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Commentable()
    {
        return $this->morphTo();
    }

    public function Replies()
    {
        return $this->hasMany(Comment::class, "replying_to");
    }

    public static function getComments($commentableClass, $commentableId)
    {
        $commentable = $commentableClass::findOrFail($commentableId);
        $comments = $commentable->comments()->get();

        return CommentResource::collection($comments);
    }
}
