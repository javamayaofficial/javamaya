<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $modelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Produk')->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str($state)->slug())),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('type')->label('Tipe')->required()->options([
                    'digital' => 'Digital (akses konten)', 'downloadable' => 'Downloadable (file)',
                    'class' => 'Kelas online', 'bundle' => 'Bundle',
                ])->live(),
                Forms\Components\Select::make('class_id')->label('Kelas terkait')
                    ->relationship('classRoom', 'title')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'class')
                    ->required(fn (Forms\Get $get) => $get('type') === 'class'),
                Forms\Components\TextInput::make('price')->label('Harga (Rp)')->required()->numeric()->minValue(0),
                Forms\Components\TextInput::make('compare_price')->label('Harga coret (Rp)')->numeric()->minValue(0),
                Forms\Components\TextInput::make('sku')->maxLength(60),
                Forms\Components\FileUpload::make('cover_path')->label('Cover')->image()->maxSize(2048)
                    ->disk('public')->directory('covers'),
                Forms\Components\RichEditor::make('description')->label('Deskripsi')->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Masa Akses')->schema([
                Forms\Components\Select::make('access_expiry_type')->label('Tipe akses')->required()->options([
                    'lifetime' => 'Selamanya (lifetime)',
                    'n_days' => 'N hari sejak pembelian',
                    'recurring_monthly' => 'Bulanan (perpanjang manual + reminder) — Tahap 2',
                    'recurring_yearly' => 'Tahunan (perpanjang manual + reminder) — Tahap 2',
                ])->default('lifetime')->live(),
                Forms\Components\TextInput::make('access_expiry_days')->label('Jumlah hari')->numeric()
                    ->minValue(1)->maxValue(3650)
                    ->visible(fn (Forms\Get $get) => $get('access_expiry_type') === 'n_days')
                    ->required(fn (Forms\Get $get) => $get('access_expiry_type') === 'n_days'),
                Forms\Components\Select::make('checkout_template')->label('Template checkout')->options([
                    'minimal' => 'Minimal', 'sidebar-sticky' => 'Sidebar Sticky',
                    'standard' => 'Standard', 'two-step' => 'Two-Step',
                ])->placeholder('Ikuti default global'),
                Forms\Components\Select::make('status')->required()->options([
                    'draft' => 'Draft', 'published' => 'Published',
                ])->default('draft'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('cover_path')->label('')->disk('public')->square(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR', locale: 'id')->sortable(),
            Tables\Columns\TextColumn::make('access_expiry_type')->label('Akses'),
            Tables\Columns\TextColumn::make('status')->badge()
                ->color(fn ($state) => $state === 'published' ? 'success' : 'gray'),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'digital' => 'Digital', 'downloadable' => 'Downloadable', 'class' => 'Kelas', 'bundle' => 'Bundle',
            ]),
            Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
        ])->actions([Tables\Actions\EditAction::make()])
          ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProductResource\RelationManagers\DownloadsRelationManager::class,
            \App\Filament\Resources\ProductResource\RelationManagers\BumpsRelationManager::class,
            \App\Filament\Resources\ProductResource\RelationManagers\BundleItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
