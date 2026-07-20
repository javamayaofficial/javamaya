<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSequenceResource\Pages;
use App\Filament\Resources\EmailSequenceResource\RelationManagers\StepsRelationManager;
use App\Models\EmailSequence;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailSequenceResource extends Resource
{
    protected static ?string $model = EmailSequence::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Email Sequences (Drip)';
    protected static ?string $modelLabel = 'Sequence';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama sequence')->required(),
            Forms\Components\Select::make('trigger_type')->label('Pemicu enroll')->required()->options([
                'purchase_product' => 'Pembelian produk',
                'lead_magnet' => 'Lead magnet',
                'manual' => 'Manual',
            ])->live(),
            Forms\Components\Select::make('trigger_id')->label('Khusus produk / magnet')
                ->options(function (Forms\Get $get) {
                    return match ($get('trigger_type')) {
                        'purchase_product' => Product::pluck('name', 'id'),
                        'lead_magnet' => \App\Models\LeadMagnet::pluck('title', 'id'),
                        default => [],
                    };
                })
                ->placeholder('Semua')->visible(fn (Forms\Get $get) => $get('trigger_type') !== 'manual'),
            Forms\Components\Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('trigger_type')->badge(),
            Tables\Columns\TextColumn::make('steps_count')->counts('steps')->label('Langkah'),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getRelations(): array { return [StepsRelationManager::class]; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailSequences::route('/'),
            'create' => Pages\CreateEmailSequence::route('/create'),
            'edit' => Pages\EditEmailSequence::route('/{record}/edit'),
        ];
    }
}
