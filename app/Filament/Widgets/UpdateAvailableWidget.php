<?php

namespace App\Filament\Widgets;

use App\Services\Update\VersionChecker;
use Filament\Widgets\Widget;

class UpdateAvailableWidget extends Widget
{
    protected static ?int $sort = 4;
    protected static string $view = 'filament.widgets.update-available';
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected function getViewData(): array
    {
        return ['check' => app(VersionChecker::class)->check(),
                'current' => app(VersionChecker::class)->currentVersion()];
    }
}
