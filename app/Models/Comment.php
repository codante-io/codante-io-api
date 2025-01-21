<?php

namespace App\Models;

use App\Http\Resources\CommentResource;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Comment extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

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
        return $this->hasMany(Comment::class, 'replying_to');
    }

    public static function getComments($commentableClass, $commentableId)
    {
        $commentable = $commentableClass::findOrFail($commentableId); // find if the commentable exists
        $comments = $commentable->comments()->get();

        return CommentResource::collection($comments);
    }

    public static function validateCommentable($commentableType)
    {
        $commentableClass = 'App\\Models\\'.$commentableType;

        $validator = Validator::make(
            ['commentable_type' => $commentableClass],
            [
                'commentable_type' => [
                    function ($attribute, $value, $fail) {
                        if (! class_exists($value)) {
                            $fail('Commentable model does not exist.');
                        } elseif (
                            ! in_array(
                                'App\\Traits\\Commentable',
                                class_uses($value)
                            )
                        ) {
                            $fail('Model is not commentable.');
                        }
                    },
                ],
            ]
        );

        $validator->validate();

        return $commentableClass;
    }

    public static function validateReply(string $replyingTo)
    {
        // if replying a reply, the comment will be replying to the father comment
        if ($replyingTo) {
            $comment = Comment::where('id', $replyingTo)->first();
            if ($comment) {
                return $comment->replying_to !== null
                    ? $comment->replying_to
                    : $replyingTo;
            }
        }

        return $replyingTo;
    }

    public function getCommentableUrl()
    {
        if ($this->commentable_type == "App\Models\ChallengeUser") {
            $challengeName = $this->commentable->challenge->slug;
            $githubUser = $this->commentable->user->github_user;

            return '/mini-projetos/'.
                $challengeName.
                '/submissoes/'.
                $githubUser;
        }

        if ($this->commentable_type == "App\Models\Lesson") {
            $workshop = $this->commentable->workshop;
            $lessonSlug = $this->commentable->slug;

            if ($workshop->is_standalone == 0) {

                return '/workshops/'.
                    $workshop->slug.
                    '/'.
                    $lessonSlug;
            }

            if ($workshop->is_standalone == 1) {
                $workshopSlug = $workshop->slug;

                return '/workshops/'.$workshopSlug.'/'.$lessonSlug;
            }
        }
    }

    // Funçoes criadas para utilizar no painel admin
    public function getCommentableType()
    {
        return class_basename($this->commentable_type);
    }

    public function getCommentableId()
    {
        return $this->commentable_id;
    }

    public function getCommentableUrlAttribute($value)
    {
        return config('app.frontend_url').$value;
    }
}
