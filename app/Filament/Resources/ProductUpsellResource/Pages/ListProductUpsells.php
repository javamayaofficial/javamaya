<?php

namespace App\Filament\Resources\ProductUpsellResource\Pages;

use App\Filament\Resources\ProductUpsellResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductUpsells extends ListRecords
{
    protected static string $resource = ProductUpsellResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
