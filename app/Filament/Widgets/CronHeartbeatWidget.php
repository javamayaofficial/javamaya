<?php

namespace App\Filament\Widgets;

use App\Models\CronHeartbeat;
use Filament\Widgets\Widget;

/** Warning bila processor tidak jalan > 10 menit + instruksi setup cron. */
class CronHeartbeatWidget extends Widget
{
    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.cron-heartbeat';
    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $seconds = CronHeartbeat::lastRanSecondsAgo();
        return [
            'seconds' => $seconds,
            'healthy' => $seconds !== null && $seconds <= 600,
            'never'   => $seconds === null,
        ];
    }
}
