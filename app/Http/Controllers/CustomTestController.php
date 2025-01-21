<?php

namespace App\Http\Controllers;

use App\Services\SyncIsProWithPlans;

class CustomTestController extends Controller
{
    public function handle()
    {
        // SyncIsProWithPlans::handle();
        return 'ok';
    }
}
