<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Support\ProcessOnVisit;
use Illuminate\Http\Request;

class CronController extends Controller
{
    /** GET /cron/run?secret=... — dipanggil cron URL tiap 1-5 menit (opsional). */
    public function run(Request $request)
    {
        $secret = (string) jm_setting('cron_secret', env('CRON_SECRET'));
        abort_unless($secret !== '' && hash_equals($secret, (string) $request->query('secret')), 403);

        $result = ProcessOnVisit::runTick('cron');
        return response()->json(['ok' => true] + $result);
    }
}
