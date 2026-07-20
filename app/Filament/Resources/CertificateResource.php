<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Kelas';
    protected static ?string $navigationLabel = 'Sertifikat Terbit';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('participant_name')->searchable(),
                Tables\Columns\TextColumn::make('classRoom.title')->label('Kelas'),
                Tables\Columns\TextColumn::make('issued_at')->dateTime('d M Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')->label('Verifikasi')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Certificate $r) => route('certificate.verify', $r->code), shouldOpenInNewTab: true),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCertificates::route('/')];
    }
}
