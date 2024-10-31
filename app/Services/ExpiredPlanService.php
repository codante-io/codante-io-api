<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;

class ExpiredPlanService
{
    public static function handle()
    {
        self::expireActiveSubscriptions();
        self::downgradeOutlierSubscriptions();
    }

    private static function expireActiveSubscriptions()
    {
        $expiredSubscriptions = Subscription::where(
            "ends_at",
            "<",
            Carbon::now()
        )
            ->where("status", "active")
            ->get();

        foreach ($expiredSubscriptions as $expiredSubscription) {
            $expiredSubscription->status = "expired";
            $expiredSubscription->save();
            $expiredSubscription->user->downgradeUserFromPro();
        }
    }

    private static function downgradeOutlierSubscriptions()
    {
        $outliersSubscriptions = Subscription::where("status", "!=", "active")
            ->join("users", "users.id", "=", "subscriptions.user_id")
            ->where("users.is_pro", true)
            ->get();

        foreach ($outliersSubscriptions as $outlierSubscription) {
            $outlierSubscription->user->downgradeUserFromPro();
        }
    }
}
