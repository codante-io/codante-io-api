<?php

namespace App\Models;

use App\Traits\Reactable;
use Auth;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Challenge extends Model
{
    use CrudTrait;
    use HasEagerLimit;
    use HasFactory;
    use Reactable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'weekly_featured_start_date' => 'datetime',
        'solution_publish_date' => 'datetime',
        'resources' => 'array',
    ];

    public function getTypeAttribute()
    {
        return 'challenge';
    }

    public function workshop()
    {
        return $this->hasOne(Workshop::class);
    }

    public function instructor()
    {
        return $this->workshop
            ? $this->workshop->instructor()
            : $this->morphTo();
    }

    public function lessons()
    {
        return $this->morphMany(Lesson::class, 'lessonable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function mainTechnology()
    {
        return $this->belongsTo(Tag::class, 'main_technology_id');
    }

    public function tracks()
    {
        return $this->morphToMany(Track::class, 'trackable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'id',
                'completed',
                'fork_url',
                'joined_discord',
                'submission_url',
                'submitted_at',
                'submission_image_url',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function scopeVisible($query)
    {
        return $query
            ->where('status', '!=', 'soon')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'archived');
    }

    public function scopeListed($query)
    {
        return $query
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'archived')
            ->where('status', '!=', 'unlisted');
    }

    public function scopeWeeklyFeatured($query)
    {
        return $query
            ->whereNotNull('weekly_featured_start_date')
            ->whereNotNull('solution_publish_date')
            ->where('weekly_featured_start_date', '<=', now())
            ->where('solution_publish_date', '>', now());
    }

    public function userJoined(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return $this->users()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function userStatus(): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        $challengeUser = $this->users()
            ->where('user_id', auth()->id())
            ->first();

        if (
            $challengeUser &&
            $challengeUser->pivot &&
            $challengeUser->pivot->completed
        ) {
            return 'completed';
        }

        if (
            $challengeUser &&
            $challengeUser->pivot &&
            $challengeUser->pivot->submitted_at !== null
        ) {
            return 'submitted';
        }

        if (
            $challengeUser &&
            $challengeUser->pivot &&
            $challengeUser->pivot->fork_url
        ) {
            return 'forked';
        }

        if (
            $challengeUser &&
            $challengeUser->pivot &&
            $challengeUser->pivot->joined_discord
        ) {
            return 'joined-discord';
        }

        if ($this->userJoined()) {
            return 'joined';
        }

        return 'not-joined';
    }

    public function hasSolution(): bool
    {
        // if there is at least one lesson with type solution, the challenge has a solution
        return $this->lessons->contains('type', 'solution');
    }

    public function getLessonSectionsArray()
    {
        $grouped = $this->lessons->groupBy('section');

        if ($grouped->count() === 1) {
            return [
                [
                    'name' => '',
                    'lesson_ids' => $grouped->first()->pluck('id'),
                ],
            ];
        }

        return $grouped
            ->map(function ($lessons, $section) {
                return [
                    'name' => $section,
                    'lesson_ids' => $lessons->pluck('id'),
                ];
            })
            ->values();
    }

    // Esse método irá trazer um array de lessons que é usado no track
    // A princípio são 2 aulas: informações do projeto, submeta sua resolução.
    // 'id' => $this->id,
    // 'name' => $this->name,
    // 'slug' => $this->slug,
    // 'url' => $url,
    // 'thumbnail_url' => $this->thumbnail_url,
    // 'user_completed' => $this->userCompleted(Auth::id()),
    // 'duration_in_seconds' => $this->duration_in_seconds,
    // 'open' => $this->canViewVideo(),
    public function getTrackLessons($trackSlug)
    {

        $userSubmitted = $this->users()
            ->where('user_id', Auth::id())
            ->where('completed', 1)
            ->exists();

        return collect([
            [
                'id' => 990,
                'name' => 'Informações do Projeto',
                'slug' => '01-informacoes-do-projeto',
                'url' => "/trilhas/$trackSlug/projeto/{$this->slug}/01-informacoes-do-projeto",
                'thumbnail_url' => null,
                'user_completed' => $userSubmitted,
                'duration_in_seconds' => null,
                'open' => true,
            ],
            [
                'id' => 991,
                'name' => 'Submeta sua Resolução',
                'slug' => '02-submeta-sua-resolucao',
                'url' => "/trilhas/$trackSlug/projeto/{$this->slug}/02-submeta-sua-resolucao",
                'thumbnail_url' => null,
                'user_completed' => $userSubmitted,
                'duration_in_seconds' => null,
                'open' => true,
            ],
        ]);
    }

    public function isWeeklyFeatured()
    {
        return $this->weekly_featured_start_date &&
            $this->weekly_featured_start_date->isPast() &&
            $this->solution_publish_date &&
            $this->solution_publish_date->isFuture();
    }

    public function setImageUrlAttribute($value)
    {
        $attribute_name = 'image_url';
        $disk = 's3';
        $destination_path = 'challenges/cover-images';

        $this->uploadFileToDisk(
            $value,
            $attribute_name,
            $disk,
            $destination_path,
            $fileName = null
        );
    }

    public function uploadFileToDisk(
        $value,
        $attribute_name,
        $disk,
        $destination_path,
        $fileName = null
    ) {
        // if a new file is uploaded, delete the previous file from the disk
        if (
            request()->hasFile($attribute_name) &&
            $this->{$attribute_name} &&
            $this->{$attribute_name} != null
        ) {
            \Storage::disk($disk)->delete(
                Str::replace(\Storage::url('/'), '', $this->{$attribute_name})
            );
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (is_null($value) && $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete(
                Str::replace(\Storage::url('/'), '', $this->{$attribute_name})
            );
            $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if (
            request()->hasFile($attribute_name) &&
            request()
                ->file($attribute_name)
                ->isValid()
        ) {
            // 1. Generate a new file name
            $file = request()->file($attribute_name);

            // use the provided file name or generate a random one
            $new_file_name =
                $fileName ??
                md5(
                    $file->getClientOriginalName().
                        random_int(1, 9999).
                        time()
                ).
                    '.'.
                    $file->getClientOriginalExtension();

            // 2. Move the new file to the correct path
            $file_path = $file->storeAs(
                $destination_path,
                $new_file_name,
                $disk
            );

            // 3. Save the complete path to the database
            $this->attributes[$attribute_name] = \Storage::url($file_path);
        }
    }
}
