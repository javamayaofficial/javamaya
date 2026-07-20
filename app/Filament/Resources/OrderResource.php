<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\Checkout\OrderPaidPipeline;
use App\Services\Checkout\RefundHandler;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $modelLabel = 'Transaksi';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_ref')->label('Ref')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('buyer_name')->label('Pembeli')->searchable()
                    ->description(fn (Order $r) => $r->buyer_email),
                Tables\Columns\TextColumn::make('gateway_code')->label('Metode')->badge(),
                Tables\Columns\TextColumn::make('total_payable')->label('Total')->money('IDR', locale: 'id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'paid' => 'success', 'pending' => 'warning', 'refunded' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'paid' => 'Paid', 'expired' => 'Expired', 'refunded' => 'Refunded',
                ]),
                Tables\Filters\SelectFilter::make('gateway_code')->label('Metode')->options([
                    'duitku' => 'Duitku', 'xendit' => 'Xendit', 'moota' => 'Moota',
                    'manual_transfer' => 'Transfer Manual', 'qris_manual' => 'QRIS Manual',
                ]),
            ])
            ->actions([
                // Konfirmasi manual (transfer/QRIS tanpa Moota) -> SATU pipeline paid yang sama
                Tables\Actions\Action::make('konfirmasi_lunas')->label('Tandai Lunas')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Order $r) => $r->isPending())
                    ->requiresConfirmation()
                    ->modalDescription(fn (Order $r) => "Konfirmasi pembayaran {$r->order_ref} sebesar " . jm_rupiah($r->total_payable) . '?')
                    ->action(function (Order $record) {
                        app(OrderPaidPipeline::class)->markPaid($record, 'manual_admin_' . auth()->id());
                        \App\Models\ActivityLog::record('order.manually_confirmed', $record);
                    }),

                // REFUND WORKFLOW: dua langkah (modal + konfirmasi), wajib reason, auto-revoke akses
                Tables\Actions\Action::make('refund')->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')->color('danger')
                    ->visible(fn (Order $r) => $r->isPaid())
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan refund')->required()->minLength(5),
                        Forms\Components\TextInput::make('amount')->label('Jumlah (Rp)')->numeric()
                            ->helperText('Kosongkan untuk refund penuh.'),
                        Forms\Components\TextInput::make('gateway_reference')->label('Ref. transaksi gateway/bank (opsional)'),
                        Forms\Components\Toggle::make('notify')->label('Kirim notifikasi ke customer')->default(true),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('Akses produk & membership dari order ini akan DICABUT otomatis. Pengembalian dana aktual dilakukan di dashboard gateway/bank Anda.')
                    ->action(function (Order $record, array $data) {
                        app(RefundHandler::class)->refund(
                            $record, $data['reason'], $data['amount'] ? (int) $data['amount'] : null,
                            auth()->id(), $data['gateway_reference'] ?? null, (bool) $data['notify'],
                        );
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make('Order')->schema([
                \Filament\Infolists\Components\TextEntry::make('order_ref'),
                \Filament\Infolists\Components\TextEntry::make('status')->badge(),
                \Filament\Infolists\Components\TextEntry::make('buyer_name'),
                \Filament\Infolists\Components\TextEntry::make('buyer_email'),
                \Filament\Infolists\Components\TextEntry::make('buyer_phone'),
                \Filament\Infolists\Components\TextEntry::make('gateway_code'),
                \Filament\Infolists\Components\TextEntry::make('subtotal')->money('IDR', locale: 'id'),
                \Filament\Infolists\Components\TextEntry::make('discount')->money('IDR', locale: 'id'),
                \Filament\Infolists\Components\TextEntry::make('tax_amount')->label('PPN')->money('IDR', locale: 'id')
                    ->helperText(fn (Order $r) => "{$r->tax_percent}% {$r->tax_mode} (snapshot)"),
                \Filament\Infolists\Components\TextEntry::make('total_payable')->money('IDR', locale: 'id'),
                \Filament\Infolists\Components\TextEntry::make('paid_at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('invoice_number'),
                \Filament\Infolists\Components\TextEntry::make('affiliate_slug')->placeholder('—'),
            ])->columns(3),
            \Filament\Infolists\Components\Section::make('Refund')->schema([
                \Filament\Infolists\Components\TextEntry::make('refund.reason'),
                \Filament\Infolists\Components\TextEntry::make('refund.amount')->money('IDR', locale: 'id'),
                \Filament\Infolists\Components\TextEntry::make('refund.refunded_at')->dateTime(),
            ])->columns(3)->visible(fn (Order $r) => $r->refund !== null),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
