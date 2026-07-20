<?php

namespace App\Filament\Resources\EmailSequenceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';
    protected static ?string $title = 'Langkah Email (drag untuk urutkan)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('delay_hours')->label('Kirim setelah (jam)')
                ->numeric()->minValue(0)->maxValue(8760)->default(24)->required()
                ->helperText('Dihitung dari langkah sebelumnya (langkah pertama: dari saat enroll).'),
            Forms\Components\TextInput::make('subject')->label('Subjek')->required(),
            Forms\Components\Textarea::make('body')->label('Isi email')->rows(8)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#'),
                Tables\Columns\TextColumn::make('delay_hours')->label('+Jam'),
                Tables\Columns\TextColumn::make('subject'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
