<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductBundle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/** Hanya tampil untuk produk type=bundle. Item bundle di-expand saat grant akses. */
class BundleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'bundleItems';
    protected static ?string $title = 'Isi Bundle';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'bundle';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('item_product_id')->label('Produk isi')
                ->options(fn () => Product::published()->where('type', '!=', 'bundle')
                    ->where('id', '!=', $this->ownerRecord->id)->pluck('name', 'id'))
                ->required()->searchable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('itemProduct.name')->label('Produk'),
            Tables\Columns\TextColumn::make('itemProduct.price')->label('Harga normal')->money('IDR', locale: 'id'),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                // pastikan bundle row induk ada
                $bundle = ProductBundle::firstOrCreate(['product_id' => $this->ownerRecord->id]);
                $data['product_bundle_id'] = $bundle->id;
                return $data;
            }),
        ])
        ->actions([Tables\Actions\DeleteAction::make()]);
    }
}
