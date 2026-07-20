<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutboundWebhookResource\Pages;
use App\Models\OutboundWebhookSubscription;
use App\Services\OutboundWebhook\EventDispatcher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OutboundWebhookResource extends Resource
{
    protected static ?string $model = OutboundWebhookSubscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';
    protected static ?string $navigationGroup = 'Integrasi & Pembayaran';
    protected static ?string $navigationLabel = 'Outbound Webhooks';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('event')->required()->options([
                'order.completed' => 'order.completed — order lunas',
                'order.refunded' => 'order.refunded — order direfund',
            ])->helperText('Event lain (class.completed, payout.paid, dll) menyusul di Tahap 3.'),
            Forms\Components\TextInput::make('url')->required()->url()->maxLength(500)
                ->placeholder('https://hooks.zapier.com/...'),
            Forms\Components\TextInput::make('secret')->required()->default(fn () => str()->random(40))
                ->helperText('Payload ditandatangani HMAC-SHA256; verifikasi header X-Javamaya-Signature.'),
            Forms\Components\Toggle::make('active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')->badge(),
                Tables\Columns\TextColumn::make('url')->limit(50),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('test')->label('Kirim test event')->icon('heroicon-o-bolt')
                    ->action(function (OutboundWebhookSubscription $record) {
                        app(EventDispatcher::class)->dispatch($record->event, [
                            'test' => true, 'order_ref' => 'JVM-TEST0001', 'total' => 150000,
                        ]);
                        Notification::make()->title('Test event diantrekan')
                            ->body('Delivery diproses maksimal 1 menit (process-on-visit/cron). Lihat log di halaman ini.')
                            ->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [\App\Filament\Resources\OutboundWebhookResource\RelationManagers\DeliveriesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutboundWebhooks::route('/'),
            'create' => Pages\CreateOutboundWebhook::route('/create'),
            'edit' => Pages\EditOutboundWebhook::route('/{record}/edit'),
        ];
    }
}
