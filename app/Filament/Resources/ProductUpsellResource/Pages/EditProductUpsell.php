<?php

namespace App\Filament\Resources\ProductUpsellResource\Pages;

use App\Filament\Resources\ProductUpsellResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductUpsell extends EditRecord
{
    protected static string $resource = ProductUpsellResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
