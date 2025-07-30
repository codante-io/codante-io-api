<?php

namespace App\Http\Controllers;

use App\Http\Resources\CouponResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['getPlanDetails']]);
    }

    public function showSubscription()
    {
        $subscriptions = Auth::user()
            ->subscriptions()
            ->orderBy('created_at', 'desc')
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
                return $subscription->status === 'active';
            })
            ->first();

        if ($activeSubscription) {
            return new SubscriptionResource($activeSubscription);
        }

        // if there is no active subscription, return the most recent
        return new SubscriptionResource($subscriptions->first());
    }

    public function getPlanDetails(Request $request)
    {
        $planId = $request->input('plan_id') ?? 1;
        $plan = Plan::find($planId);

        // checa se o cupom existe, senão erro
        $couponCode = $request->input('coupon');

        $couponInfo = null;
        if ($couponCode) {
            $coupon = (new Coupon)->getValidCoupon($couponCode, $planId);

            $couponInfo = $coupon
                ? new CouponResource($coupon)
                : [
                    'error' => true,
                    'message' => 'Cupom inválido',
                ];
        }

        //retorna o plano + detalhes do cupom
        return response()->json([
            'coupon' => [$couponCode ? $couponInfo : null],
            'plan' => new PlanResource($plan),
        ]);
    }
}
