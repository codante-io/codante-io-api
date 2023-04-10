<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
