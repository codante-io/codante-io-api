<?php

namespace App\Http\Controllers;

use App\Mail\UserSubscribedToPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SyncIsProWithPlans;
use Illuminate\Http\Request;
use Mail;

class CustomTestController extends Controller
{
    public function handle()
    {
        // SyncIsProWithPlans::handle();
        return "ok";
    }
}
