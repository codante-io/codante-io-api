<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Trackable extends MorphPivot
{
    use HasFactory;

    protected $table = "trackables";

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
