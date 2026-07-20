<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductUpsellResource\Pages;
use App\Models\Product;
use App\Models\ProductUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductUpsellResource extends Resource
{
    protected static ?string $model = ProductUpsell::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $navigationLabel = 'Upsell Pasca-Beli';
    protected static ?string $modelLabel = 'Upsell';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('main_product_id')->label('Setelah membeli (produk pemicu)')
                ->options(fn () => Product::published()->pluck('name', 'id'))->required()
                ->unique(ignoreRecord: true)->searchable()
                ->helperText('Satu produk pemicu = satu tawaran upsell.'),
            Forms\Components\Select::make('upsell_product_id')->label('Tawarkan produk')
                ->options(fn () => Product::published()->pluck('name', 'id'))->required()->searchable()->live(),
            Forms\Components\TextInput::make('upsell_price')->label('Harga spesial (Rp)')->required()->numeric()->minValue(0)
                ->helperText(fn (Forms\Get $get) => ($p = Product::find($get('upsell_product_id')))
                    ? 'Harga normal: ' . jm_rupiah($p->price) : ''),
            Forms\Components\TextInput::make('headline')->required()->maxLength(200)
                ->placeholder('Tunggu! Lengkapi dengan paket lanjutan diskon 50%'),
            Forms\Components\Textarea::make('description')->maxLength(500),
            Forms\Components\Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mainProduct.name')->label('Setelah beli'),
            Tables\Columns\TextColumn::make('upsellProduct.name')->label('Tawarkan'),
            Tables\Columns\TextColumn::make('upsell_price')->label('Harga spesial')->money('IDR', locale: 'id'),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductUpsells::route('/'),
            'create' => Pages\CreateProductUpsell::route('/create'),
            'edit' => Pages\EditProductUpsell::route('/{record}/edit'),
        ];
    }
}
