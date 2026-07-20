<?php

namespace App\Filament\Resources\ClassResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';
    protected static ?string $title = 'Materi (drag untuk urutkan)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul materi')->required(),
            Forms\Components\Select::make('type')->required()->options([
                'video_url' => 'Video (URL embed YouTube/Vimeo/Bunny)',
                'pdf' => 'PDF (URL/route file)', 'embed' => 'Embed HTML', 'text' => 'Teks',
            ])->live(),
            Forms\Components\Textarea::make('content')->label('Konten / URL')->required()
                ->helperText('video_url: gunakan URL embed (https://www.youtube.com/embed/xxxx).'),
            Forms\Components\Toggle::make('is_preview')->label('Bisa dilihat sebelum beli (preview)'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_preview')->boolean()->label('Preview'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
