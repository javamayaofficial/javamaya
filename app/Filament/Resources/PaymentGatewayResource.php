<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use App\Services\PaymentGateways\GatewayRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Halaman Payment Gateways TERPISAH: toggle on/off + kredensial + sandbox + fee display. */
class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Integrasi & Pembayaran';
    protected static ?string $navigationLabel = 'Payment Gateways';
    protected static ?string $modelLabel = 'Payment Gateway';

    public static function canCreate(): bool { return false; }   // 5 gateway dari seeder; tambah via rilis

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama tampilan')->required(),
            Forms\Components\Toggle::make('active')->label('Aktif (tampil di checkout)'),
            Forms\Components\Toggle::make('sandbox_mode')->label('Sandbox mode')
                ->helperText('Aktifkan untuk uji coba tanpa uang nyata (Duitku/Xendit).'),
            Forms\Components\TextInput::make('fee_display')->label('Info biaya (tampil ke pembeli)')
                ->placeholder('cth: Gratis biaya / +Rp 4.000'),
            Forms\Components\KeyValue::make('credentials')->label('Kredensial')
                ->keyLabel('Kunci')->valueLabel('Nilai')
                ->helperText('duitku: merchant_code, api_key • xendit: api_key, webhook_token • moota: webhook_secret, api_token (opsional). Tersimpan terenkripsi. Manual/QRIS diisi di Settings.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Gateway'),
                Tables\Columns\TextColumn::make('code')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('active')->label('Aktif')->boolean(),
                Tables\Columns\IconColumn::make('sandbox_mode')->label('Sandbox')->boolean(),
                Tables\Columns\TextColumn::make('kesiapan')->label('Kredensial')
                    ->state(fn (PaymentGateway $r) => app(GatewayRegistry::class)->driver($r->code)?->isConfigured() ? 'Lengkap' : 'Belum lengkap')
                    ->badge()->color(fn ($state) => $state === 'Lengkap' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('fee_display')->label('Biaya')->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test')->label('Test')->icon('heroicon-o-bolt')
                    ->action(function (PaymentGateway $record) {
                        $driver = app(GatewayRegistry::class)->driver($record->code);
                        $ok = $driver?->isConfigured() ?? false;
                        Notification::make()
                            ->title($ok ? 'Kredensial lengkap ✓' : 'Kredensial belum lengkap')
                            ->body($ok
                                ? 'Gateway siap dipakai. Lakukan checkout uji dengan Sandbox mode ON untuk tes end-to-end.'
                                : 'Lengkapi kredensial sesuai petunjuk di kolom Kredensial, lalu tes ulang.')
                            ->{$ok ? 'success' : 'warning'}()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGateways::route('/'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
