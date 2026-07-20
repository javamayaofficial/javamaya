<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\ProductReview;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Ulasan (Moderasi)';
    protected static ?string $modelLabel = 'Ulasan';

    public static function canCreate(): bool { return false; }

    public static function getNavigationBadge(): ?string
    {
        $pending = ProductReview::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Produk'),
                Tables\Columns\TextColumn::make('user.name')->label('Pembeli'),
                Tables\Columns\TextColumn::make('rating')->formatStateUsing(fn ($state) => str_repeat('★', $state)),
                Tables\Columns\TextColumn::make('body')->limit(60)->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', default => 'danger',
                }),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options([
                'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected',
            ])])
            ->actions([
                Tables\Actions\Action::make('approve')->label('Setujui')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (ProductReview $r) => $r->status !== 'approved')
                    ->action(fn (ProductReview $record) => $record->update(['status' => 'approved'])),
                Tables\Actions\Action::make('reject')->label('Tolak')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn (ProductReview $r) => $r->status !== 'rejected')
                    ->action(fn (ProductReview $record) => $record->update(['status' => 'rejected'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_bulk')->label('Setujui terpilih')
                    ->action(fn ($records) => $records->each->update(['status' => 'approved'])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProductReviews::route('/')];
    }
}
