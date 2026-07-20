<?php

namespace App\Filament\Resources\BroadcastCampaignResource\Pages;

use App\Filament\Resources\BroadcastCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBroadcastCampaign extends EditRecord
{
    protected static string $resource = BroadcastCampaignResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
