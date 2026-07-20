<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentPageResource\Pages;
use App\Models\ContentPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Halaman Statis (CMS)';
    protected static ?string $modelLabel = 'Halaman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()
                ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)
                ->helperText('URL: /page/{slug}'),
            Forms\Components\MarkdownEditor::make('body')->required()->columnSpanFull(),
            Forms\Components\Toggle::make('published')->default(true),
            Forms\Components\Toggle::make('show_in_footer')->label('Tampilkan di footer')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('slug'),
            Tables\Columns\IconColumn::make('published')->boolean(),
            Tables\Columns\IconColumn::make('show_in_footer')->boolean()->label('Footer'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentPages::route('/'),
            'create' => Pages\CreateContentPage::route('/create'),
            'edit' => Pages\EditContentPage::route('/{record}/edit'),
        ];
    }
}
