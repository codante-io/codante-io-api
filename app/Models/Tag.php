<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function workshops()
    {
        return $this->morphedByMany(Workshop::class, 'taggable');
    }

    public function challenges()
    {
        return $this->morphedByMany(Challenge::class, 'taggable');
    }
}
