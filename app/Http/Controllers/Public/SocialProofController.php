<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Marketing\SocialProofFeed;

class SocialProofController extends Controller
{
    public function feed(SocialProofFeed $feed)
    {
        return response()->json(['data' => $feed->recent()])
            ->header('Cache-Control', 'public, max-age=60');
    }
}
