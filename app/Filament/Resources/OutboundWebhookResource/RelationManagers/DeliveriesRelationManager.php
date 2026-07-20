<?php

namespace App\Filament\Resources\OutboundWebhookResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';
    protected static ?string $title = 'Delivery Log (per attempt)';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('event'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'success' => 'success', 'retrying' => 'warning', 'dead' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('attempt_count')->label('Attempts'),
                Tables\Columns\TextColumn::make('response_code')->placeholder('—'),
                Tables\Columns\TextColumn::make('next_retry_at')->dateTime('d M H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('last_error')->limit(40)->placeholder('—'),
            ]);
    }
}
