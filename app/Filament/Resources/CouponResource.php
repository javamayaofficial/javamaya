<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $modelLabel = 'Kupon';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->maxLength(40)
                ->unique(ignoreRecord: true)->dehydrateStateUsing(fn ($state) => strtoupper($state)),
            Forms\Components\Select::make('type')->required()->options(['fixed' => 'Nominal (Rp)', 'percentage' => 'Persentase (%)'])->live(),
            Forms\Components\TextInput::make('value')->required()->numeric()->minValue(1)
                ->maxValue(fn (Forms\Get $get) => $get('type') === 'percentage' ? 100 : null),
            Forms\Components\TextInput::make('max_uses_total')->label('Batas total pakai')->numeric()->minValue(1),
            Forms\Components\TextInput::make('max_uses_per_user')->label('Batas per user')->numeric()->minValue(1),
            Forms\Components\Select::make('product_ids')->label('Hanya untuk produk')->multiple()
                ->options(fn () => \App\Models\Product::pluck('name', 'id'))->placeholder('Semua produk'),
            Forms\Components\Select::make('excluded_product_ids')->label('Kecuali produk')->multiple()
                ->options(fn () => \App\Models\Product::pluck('name', 'id')),
            Forms\Components\DateTimePicker::make('starts_at')->label('Mulai'),
            Forms\Components\DateTimePicker::make('expires_at')->label('Berakhir'),
            Forms\Components\Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('value'),
            Tables\Columns\TextColumn::make('usages_count')->counts('usages')->label('Dipakai'),
            Tables\Columns\IconColumn::make('active')->boolean(),
            Tables\Columns\TextColumn::make('expires_at')->dateTime('d M Y')->placeholder('—'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
