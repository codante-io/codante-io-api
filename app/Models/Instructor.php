<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use CrudTrait;
    use HasFactory;

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
