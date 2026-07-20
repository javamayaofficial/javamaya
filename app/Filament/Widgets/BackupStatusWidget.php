<?php

namespace App\Filament\Widgets;

use App\Models\Backup;
use Filament\Widgets\Widget;

class BackupStatusWidget extends Widget
{
    protected static ?int $sort = 3;
    protected static string $view = 'filament.widgets.backup-status';
    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $last = Backup::where('status', 'success')->latest()->first();
        return [
            'last' => $last,
            'stale' => $last ? $last->created_at->lt(now()->subDays(7)) : true,
        ];
    }
}
