<?php

namespace App\Models;

use App\Traits\Reactable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChallengeUser extends Model
{
    use HasFactory;
    use Reactable;
    use SoftDeletes;

    protected $table = "challenge_user";

    function User()
    {
        return $this->belongsTo(User::class);
    }

    function Challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
