<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadMagnetResource\Pages;
use App\Models\LeadMagnet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadMagnetResource extends Resource
{
    protected static ?string $model = LeadMagnet::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Lead Magnets';
    protected static ?string $modelLabel = 'Lead Magnet';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul')->required()
                ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)
                ->helperText(fn (?LeadMagnet $record) => 'Landing page: ' . url('/go/' . ($record?->slug ?? '{slug}'))),
            Forms\Components\TextInput::make('deliverable_url')->label('URL deliverable (ebook/video/grup)')->url()->required(),
            Forms\Components\Toggle::make('active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\TextColumn::make('slug')->formatStateUsing(fn ($state) => '/go/' . $state)->copyable(),
            Tables\Columns\TextColumn::make('leads')->label('Leads verified')
                ->state(fn (LeadMagnet $r) => \App\Models\Lead::where('lead_magnet_id', $r->id)->where('verified', true)->count()),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadMagnets::route('/'),
            'create' => Pages\CreateLeadMagnet::route('/create'),
            'edit' => Pages\EditLeadMagnet::route('/{record}/edit'),
        ];
    }
}
