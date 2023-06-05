<?php

namespace App\Models;

use App\Traits\Reactable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function setImageAttribute($value)
    {
        $attribute_name = "image_url";
        $disk = "s3";
        $destination_path = "challenges/cover-images";
        $fileName = "$this->id";
        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path, $fileName);
    }
}
