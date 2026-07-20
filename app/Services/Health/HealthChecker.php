<?php

namespace App\Services\Health;

use App\Models\CronHeartbeat;
use Illuminate\Support\Facades\DB;

/** /healthz: JSON status; 200 sehat, 503 bila DB atau storage bermasalah. */
class HealthChecker
{
    /** @return array{status: int, body: array} */
    public function check(): array
    {
        $dbOk = true;
        try { DB::select('SELECT 1'); } catch (\Throwable) { $dbOk = false; }

        $storageWritable = is_writable(storage_path('app'));

        $queueOk = true;
        try { DB::table('jobs')->count(); } catch (\Throwable) { $queueOk = false; }

        $cronSeconds = null;
        try { $cronSeconds = CronHeartbeat::lastRanSecondsAgo(); } catch (\Throwable) {}

        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());
        $diskFreePercent = ($free !== false && $total) ? (int) round($free / $total * 100) : null;

        $critical = ! $dbOk || ! $storageWritable;
        return [
            'status' => $critical ? 503 : 200,
            'body' => [
                'db_ok'                     => $dbOk,
                'storage_writable'          => $storageWritable,
                'queue_ok'                  => $queueOk,
                'cron_last_ran_seconds_ago' => $cronSeconds,
                'disk_free_percent'         => $diskFreePercent,
                'version'                   => config('javamaya.version'),
            ],
        ];
    }
}
