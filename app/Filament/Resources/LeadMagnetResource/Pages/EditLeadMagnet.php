<?php

namespace App\Filament\Resources\LeadMagnetResource\Pages;

use App\Filament\Resources\LeadMagnetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadMagnet extends EditRecord
{
    protected static string $resource = LeadMagnetResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
