<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Activity Logs';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('action')->badge()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Oleh')->placeholder('Sistem'),
                Tables\Columns\TextColumn::make('subject_type')->formatStateUsing(fn ($state) => class_basename($state ?? ''))->placeholder('—'),
                Tables\Columns\TextColumn::make('ip')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\Filter::make('hari_ini')->query(fn ($q) => $q->whereDate('created_at', today())),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/')];
    }
}
