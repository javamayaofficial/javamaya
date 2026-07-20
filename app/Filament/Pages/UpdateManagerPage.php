<?php

namespace App\Filament\Pages;

use App\Models\UpdateHistory;
use App\Services\Update\AutoUpdatePipeline;
use App\Services\Update\UpdateManager;
use App\Services\Update\VersionChecker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** Update Manager Tahap 1: versi sekarang, Cek Update, changelog, link ZIP, history, finalize manual. */
class UpdateManagerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Update Sistem';
    protected static ?string $title = 'Update Manager';
    protected static string $view = 'filament.pages.update-manager';

    public string $current = '';
    public array $check = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(VersionChecker $checker): void
    {
        $this->current = $checker->currentVersion();
        $this->check = $checker->check();
    }

    public function getViewData(): array
    {
        return ['history' => UpdateHistory::latest()->limit(20)->get()];
    }

    public function checkNow(): void
    {
        $this->check = app(VersionChecker::class)->check(force: true);
        Notification::make()->title('Pengecekan selesai')->success()->send();
    }

    public function finalizeManual(): void
    {
        app(UpdateManager::class)->finalizeManualUpdate($this->current);
        $this->current = app(VersionChecker::class)->currentVersion();
        Notification::make()->title('Migrasi & cache selesai')
            ->body('Update manual difinalisasi dan tercatat di riwayat.')->success()->send();
    }

    /** Update otomatis 1-klik: backup -> unduh -> verifikasi -> swap -> migrasi -> rollback bila gagal. */
    public function runAutoUpdate(): void
    {
        @set_time_limit(600);
        $result = app(AutoUpdatePipeline::class)->run();

        $this->current = app(VersionChecker::class)->currentVersion();
        $this->check = app(VersionChecker::class)->check(force: true);

        if ($result['ok']) {
            Notification::make()->title('Update berhasil')
                ->body($result['message'])->success()->persistent()->send();
        } elseif (($result['status'] ?? '') === 'rolled_back') {
            Notification::make()->title('Update gagal — sistem dipulihkan')
                ->body($result['message'])->warning()->persistent()->send();
        } else {
            Notification::make()->title('Update tidak dijalankan')
                ->body($result['message'])->danger()->persistent()->send();
        }
    }
}
