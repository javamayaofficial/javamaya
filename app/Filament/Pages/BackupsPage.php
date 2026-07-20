<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\DatabaseBackup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BackupsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Backups';
    protected static ?string $title = 'Backups';
    protected static string $view = 'filament.pages.backups';

    public function getViewData(): array
    {
        return ['backups' => Backup::latest()->limit(30)->get(),
                'retention' => config('javamaya.backup.retention')];
    }

    public function backupNow(): void
    {
        $result = app(DatabaseBackup::class)->run();
        ActivityLog::record('backup.manual', null, $result);
        Notification::make()
            ->title($result['ok'] ? 'Backup database berhasil' : 'Backup gagal')
            ->body($result['ok'] ? 'Ukuran: ' . number_format(($result['size'] ?? 0) / 1024, 0) . ' KB' : ($result['error'] ?? ''))
            ->{$result['ok'] ? 'success' : 'danger'}()->send();
    }

    public function download(int $id)
    {
        $backup = Backup::findOrFail($id);
        $abs = storage_path('app/' . $backup->path);
        abort_unless(is_file($abs), 404, 'File backup tidak ditemukan.');
        return response()->download($abs);
    }
}
