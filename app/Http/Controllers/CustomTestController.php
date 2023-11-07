<?php

namespace App\Http\Controllers;

use App\Mail\UserSubscribedToPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class CustomTestController extends Controller
{
    public function handle()
    {
        $user = User::find(395);
        $subscription = Subscription::find(1);

        Mail::to($user->email)->send(
            new UserSubscribedToPlan($user, $subscription)
        );
    }
}
