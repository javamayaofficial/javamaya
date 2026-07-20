<?php

namespace App\Filament\Resources\BroadcastCampaignResource\Pages;

use App\Filament\Resources\BroadcastCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBroadcastCampaigns extends ListRecords
{
    protected static string $resource = BroadcastCampaignResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
