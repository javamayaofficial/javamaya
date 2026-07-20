<?php

namespace App\Filament\Resources\SalesPageResource\Pages;

use App\Filament\Resources\SalesPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesPage extends EditRecord
{
    protected static string $resource = SalesPageResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SalesPageResource::mergeEmbed($data, $this->data['embed_html'] ?? null);
    }
}
