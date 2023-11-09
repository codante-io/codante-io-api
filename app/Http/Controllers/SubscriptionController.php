<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum");
    }

    public function showSubscription()
    {
        $subscriptions = Auth::user()
            ->subscriptions()
            ->orderBy("created_at", "desc")
            ->get();

        // if there is no subscription, return null
        if ($subscriptions->count() === 0) {
            return null;
        }

        // if there is only one subscription, return this subscription
        if ($subscriptions->count() === 1) {
            return new SubscriptionResource($subscriptions->first());
        }

        // if there is more than one subscription, return the active one
        $activeSubscription = $subscriptions
            ->filter(function ($subscription) {
                return $subscription->status === "active";
            })
            ->first();

        if ($activeSubscription) {
            return new SubscriptionResource($activeSubscription);
        }

        // if there is no active subscription, return the most recent
        return new SubscriptionResource($subscriptions->first());
    }
}
