<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;

class SyncIsProWithPlans
{
    public static function handle()
    {
        // we need to check whether user is pro. If he is, he must have an active subscription.
        // If this is not the case we must downgrade him from pro.
        $proUsers = User::where("is_pro", true)
            ->select("id")
            ->get();

        foreach ($proUsers as $proUser) {
            $subscription = Subscription::where("user_id", $proUser->id)
                ->where("status", "active")
                ->first();

            if (!$subscription) {
                $proUser->downgradeUserFromPro();
            }
        }
    }
}
