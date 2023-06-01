<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public static $allowedReactionTypes = [
        "like",
        "exploding_head",
        "fire",
        "rocket",
    ];
}
