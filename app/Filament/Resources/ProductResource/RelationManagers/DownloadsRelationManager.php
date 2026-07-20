<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DownloadsRelationManager extends RelationManager
{
    protected static string $relationship = 'downloads';
    protected static ?string $title = 'File Download (terproteksi token)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')->label('Nama file tampilan')->required(),
            Forms\Components\FileUpload::make('file_path')->label('File')->required()
                ->disk('local')->directory('downloads')->preserveFilenames()
                ->maxSize(512000)   // 500 MB
                ->acceptedFileTypes(['application/pdf', 'application/zip', 'application/x-zip-compressed',
                    'audio/mpeg', 'video/mp4', 'image/png', 'image/jpeg',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->helperText('Disimpan di storage privat (bukan folder publik). Diunduh pembeli via token 15 menit.')
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if ($state) $set('file_size', $state->getSize());
                }),
            Forms\Components\Hidden::make('file_size')->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('label'),
            Tables\Columns\TextColumn::make('file_size')->label('Ukuran')
                ->formatStateUsing(fn ($state) => number_format($state / 1048576, 1) . ' MB'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y'),
        ])
        ->headerActions([Tables\Actions\CreateAction::make()])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
