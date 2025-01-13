<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'links' => 'array',
    ];

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
