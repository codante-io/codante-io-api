<?php

namespace App\Models;

use App\Traits\Commentable;
use App\Traits\Reactable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChallengeUser extends Model
{
    use HasFactory;
    use Reactable;
    use SoftDeletes;
    use Commentable;
    use CrudTrait;

    protected $table = "challenge_user";
    protected $fillable = ["listed"];

    function User()
    {
        return $this->belongsTo(User::class);
    }

    function Challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    function Certificate()
    {
        return $this->morphOne(Certificate::class, "certifiable");
    }
}
