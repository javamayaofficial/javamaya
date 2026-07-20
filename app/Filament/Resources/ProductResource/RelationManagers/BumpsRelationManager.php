<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BumpsRelationManager extends RelationManager
{
    protected static string $relationship = 'bumps';
    protected static ?string $title = 'Order Bump (tawaran di form checkout)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('bump_product_id')->label('Produk bump')
                ->options(fn () => Product::published()->where('id', '!=', $this->ownerRecord->id)->pluck('name', 'id'))
                ->required()->searchable(),
            Forms\Components\TextInput::make('bump_price')->label('Harga bump (Rp)')->required()->numeric()->minValue(0),
            Forms\Components\TextInput::make('headline')->required()->maxLength(150)
                ->placeholder('Tambahkan template premium — diskon 60% khusus sekarang'),
            Forms\Components\Toggle::make('active')->default(true)
                ->helperText('Hanya satu bump aktif yang tampil di checkout.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('bumpProduct.name')->label('Produk'),
            Tables\Columns\TextColumn::make('bump_price')->money('IDR', locale: 'id'),
            Tables\Columns\TextColumn::make('headline')->limit(40),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])
        ->headerActions([Tables\Actions\CreateAction::make()])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
