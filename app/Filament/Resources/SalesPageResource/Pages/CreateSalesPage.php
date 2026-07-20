<?php

namespace App\Filament\Resources\SalesPageResource\Pages;

use App\Filament\Resources\SalesPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesPage extends CreateRecord
{
    protected static string $resource = SalesPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SalesPageResource::mergeEmbed($data, $this->data['embed_html'] ?? null);
    }
}
