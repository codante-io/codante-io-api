<?php

namespace App\Models;

use App\Traits\Reactable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeUser extends Model
{
    use HasFactory;
    use Reactable;
    protected $table = "challenge_user";
}
