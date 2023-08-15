<?php

namespace App\Http\Controllers;

use App\Models\UserActionPoints;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function getRanking(Request $request)
    {
        $monthly = $request->monthly;

        $ranking = UserActionPoints::calculateRanking($monthly);
        return response()->json([
            "data" => $ranking,
        ]);
    }
}
