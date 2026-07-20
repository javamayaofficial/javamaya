<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductWaitlistResource\Pages;
use App\Models\Product;
use App\Models\ProductWaitlist;
use App\Services\Marketing\WaitlistService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductWaitlistResource extends Resource
{
    protected static ?string $model = ProductWaitlist::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $navigationLabel = 'Waitlist Produk';
    protected static ?string $modelLabel = 'Waitlist';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Produk')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('whatsapp')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'waiting' => 'warning', 'notified' => 'info', 'converted' => 'success', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Daftar')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->label('Produk')
                    ->options(fn () => Product::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('status')->options([
                    'waiting' => 'Menunggu', 'notified' => 'Sudah dikabari', 'converted' => 'Membeli',
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('notify_restock')->label('Kabari Restock')->icon('heroicon-o-megaphone')
                    ->form([
                        \Filament\Forms\Components\Select::make('product_id')->label('Produk yang restock')
                            ->options(fn () => Product::whereHas('waitlist', fn ($q) => $q->where('status', 'waiting'))->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $count = app(WaitlistService::class)->notifyWaiting(Product::findOrFail($data['product_id']));
                        Notification::make()->title("Notifikasi terkirim ke {$count} orang")
                            ->body('Batch maksimal 50 per klik agar aman dari limit provider WA.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProductWaitlists::route('/')];
    }
}
