<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use App\Services\PublicApi\ApiKeyManager;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Integrasi & Pembayaran';
    protected static ?string $navigationLabel = 'API Keys (REST API)';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('key_prefix')->label('Key')->formatStateUsing(fn ($state) => $state . '••••••••'),
                Tables\Columns\TextColumn::make('scopes')->badge(),
                Tables\Columns\TextColumn::make('rate_limit_per_minute')->label('Limit/menit'),
                Tables\Columns\TextColumn::make('last_used_at')->dateTime('d M H:i')->placeholder('Belum dipakai'),
                Tables\Columns\TextColumn::make('revoked_at')->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Revoked' : 'Aktif')
                    ->badge()->color(fn ($state) => $state ? 'danger' : 'success')->default('Aktif'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('buat')->label('Generate API Key')->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\TextInput::make('name')->label('Nama key')->required()
                            ->placeholder('cth: Zapier - Google Sheets'),
                        Forms\Components\CheckboxList::make('scopes')->required()
                            ->options(array_combine(ApiKeyManager::SCOPES, ApiKeyManager::SCOPES)),
                        Forms\Components\TextInput::make('rate_limit')->numeric()->default(60)->minValue(1)->maxValue(600),
                    ])
                    ->action(function (array $data) {
                        $result = app(ApiKeyManager::class)->create($data['name'], $data['scopes'], (int) $data['rate_limit']);
                        Notification::make()->title('API Key dibuat — salin SEKARANG (tidak akan tampil lagi)')
                            ->body($result['plain'])->success()->persistent()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')->label('Revoke')->color('danger')->icon('heroicon-o-no-symbol')
                    ->visible(fn (ApiKey $r) => $r->revoked_at === null)
                    ->requiresConfirmation()
                    ->action(fn (ApiKey $record) => app(ApiKeyManager::class)->revoke($record)),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListApiKeys::route('/')];
    }
}
