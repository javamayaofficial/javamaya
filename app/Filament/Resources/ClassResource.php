<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassResource\Pages;
use App\Filament\Resources\ClassResource\RelationManagers\MaterialsRelationManager;
use App\Models\ClassRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassResource extends Resource
{
    protected static ?string $model = ClassRoom::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Kelas';
    protected static ?string $modelLabel = 'Kelas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul')->required()
                ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('class_category_id')->label('Kategori')
                ->relationship('category', 'name')->createOptionForm([
                    Forms\Components\TextInput::make('name')->required()
                        ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
                    Forms\Components\TextInput::make('slug')->required(),
                ]),
            Forms\Components\Toggle::make('certificate_enabled')->label('Certificate otomatis saat 100%')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('materials_count')->counts('materials')->label('Materi'),
            Tables\Columns\IconColumn::make('certificate_enabled')->label('Certificate')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getRelations(): array { return [MaterialsRelationManager::class]; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClassRoom::route('/create'),
            'edit' => Pages\EditClassRoom::route('/{record}/edit'),
        ];
    }
}
