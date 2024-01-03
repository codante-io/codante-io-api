<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Traits\Reactable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use CrudTrait;
    use HasFactory;
    use Reactable;
    use SoftDeletes;

    protected $guarded = ["id"];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }
}
