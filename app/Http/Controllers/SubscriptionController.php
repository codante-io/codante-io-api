<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe()
    {
        $user = Auth::user();
        $user->subscribeToPlan(1);
    }
}
