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

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['completed_at']);
    }

    public function userCompleted(User $user, bool $setComplete = true)
    {
        if (!$setComplete) {
            $this->users()->detach($user->id);
            return;
        }
        $this->users()->syncWithoutDetaching([
            $user->id => [
                'completed_at' => now(),
            ],
        ]);
    }
}
