<?php

namespace App\Models;

use App\Traits\Reactable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Challenge extends Model
{
    use CrudTrait;
    use HasFactory;
    use Reactable;

    protected $guarded = ["id"];
    protected $casts = [
        "published_at" => "datetime",
    ];

    public function getTypeAttribute()
    {
        return "challenge";
    }

    public function workshop()
    {
        return $this->hasOne(Workshop::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function tracks()
    {
        return $this->morphToMany(Track::class, "trackable");
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot([
            "id",
            "completed",
            "fork_url",
            "joined_discord",
            "submission_url",
            "submission_image_url",
        ]);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function setImageUrlAttribute($value)
    {
        $attribute_name = "image_url";
        $disk = "s3";
        $destination_path = "challenges/cover-images";

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
            \Storage::disk($disk)->delete(Str::replace(\Storage::url("/"), "", $this->{$attribute_name}));
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (is_null($value) && $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete(
                Str::replace(\Storage::url("/"), "", $this->{$attribute_name})
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
                    $file->getClientOriginalName() .
                        random_int(1, 9999) .
                        time()
                ) .
                "." .
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
