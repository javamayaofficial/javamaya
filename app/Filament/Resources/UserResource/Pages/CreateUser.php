<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\StaffPermission;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->syncPermissions();
    }

    protected function syncPermissions(): void
    {
        $perms = (array) ($this->form->getRawState()['permissions'] ?? []);
        if ($this->record->role === 'staff') {
            foreach ($perms as $p) {
                StaffPermission::firstOrCreate(['user_id' => $this->record->id, 'permission' => $p]);
            }
        }
    }
}
