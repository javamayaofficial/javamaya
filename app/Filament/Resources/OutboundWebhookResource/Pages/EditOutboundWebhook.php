<?php

namespace App\Filament\Resources\OutboundWebhookResource\Pages;

use App\Filament\Resources\OutboundWebhookResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOutboundWebhook extends EditRecord
{
    protected static string $resource = OutboundWebhookResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
