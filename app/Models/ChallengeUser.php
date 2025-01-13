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
    use Commentable;
    use CrudTrait;
    use HasFactory;
    use Reactable;
    use SoftDeletes;

    protected $table = 'challenge_user';

    protected $fillable = ['listed'];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function Certificate()
    {
        return $this->morphOne(Certificate::class, 'certifiable');
    }
}
