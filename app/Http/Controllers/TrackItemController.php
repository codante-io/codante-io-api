<?php

namespace App\Http\Controllers;

use App\Http\Resources\Tracks\TrackItemResource;
use App\Models\TrackItem;

class TrackItemController extends Controller
{
    public function index()
    {
        return TrackItemResource::collection(
            TrackItem::query()
                ->where('status', 'published')
                ->orWhere('status', 'soon')
                ->with('tags')
                ->get()
        );
    }

    public function show($slug)
    {
        return new TrackItemResource(
            TrackItem::query()
                ->where('slug', $slug)
                ->with('tags')
                ->firstOrFail()
        );
    }
}
