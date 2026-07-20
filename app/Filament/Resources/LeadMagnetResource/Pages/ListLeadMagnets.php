<?php

namespace App\Filament\Resources\LeadMagnetResource\Pages;

use App\Filament\Resources\LeadMagnetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadMagnets extends ListRecords
{
    protected static string $resource = LeadMagnetResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
