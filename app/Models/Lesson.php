<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}
