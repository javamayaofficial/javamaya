<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\StaffPermission;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $perms = (array) ($this->form->getRawState()['permissions'] ?? []);
        StaffPermission::where('user_id', $this->record->id)->delete();
        if ($this->record->role === 'staff') {
            foreach ($perms as $p) {
                StaffPermission::create(['user_id' => $this->record->id, 'permission' => $p]);
            }
        }
    }
}
