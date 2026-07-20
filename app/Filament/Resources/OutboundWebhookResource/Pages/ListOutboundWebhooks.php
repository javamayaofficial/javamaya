<?php

namespace App\Filament\Resources\OutboundWebhookResource\Pages;

use App\Filament\Resources\OutboundWebhookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutboundWebhooks extends ListRecords
{
    protected static string $resource = OutboundWebhookResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
