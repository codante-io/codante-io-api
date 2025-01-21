<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionPoints extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $guarded = ['id'];

    public static function awardPoints(
        $userId,
        $actionName,
        $points,
        $pointableId = null,
        $pointableType = null
    ) {
        UserActionPoints::create([
            'user_id' => $userId,
            'pointable_id' => $pointableId,
            'pointable_type' => $pointableType,
            'action_name' => $actionName,
            'points' => $points,
        ]);
    }

    public static function removePoints(
        $userId,
        $actionName,
        $pointableId,
        $pointableType
    ) {
        $record = UserActionPoints::where('user_id', $userId)
            ->where('action_name', $actionName)
            ->where('pointable_id', $pointableId)
            ->where('pointable_type', $pointableType)
            ->first();

        if ($record) {
            $record->delete();
        }
    }

    public static function calculateRanking($monthly)
    {
        $query = UserActionPoints::selectRaw(
            'users.name, '.
                'users.is_pro, '.
                'users.is_admin, '.
                'users.avatar_url, '.
                'users.settings, '.
                'sum(user_action_points.points) as points, '.
                "SUM(CASE WHEN user_action_points.action_name = 'challenge_completed' THEN 1 ELSE 0 END) AS completed_challenge_count, ".
                "SUM(CASE WHEN user_action_points.action_name = 'reaction_received' THEN 1 ELSE 0 END) AS received_reaction_count"
        )
            ->where('users.is_admin', false)
            ->join('users', 'users.id', '=', 'user_action_points.user_id')
            ->groupBy(
                'users.name',
                'users.avatar_url',
                'users.is_pro',
                'users.is_admin',
                'users.settings'
            )
            ->orderByDesc('points')
            ->limit(10);

        if ($monthly) {
            $query->whereMonth('user_action_points.created_at', date('m'));
        }

        $users = $query->get();

        // make users->settings as array
        $users->map(function ($user) {
            if ($user->settings && is_string($user->settings)) {
                $user->settings = json_decode($user->settings, true);
            }

            return $user;
        });

        return $users;
    }
}
