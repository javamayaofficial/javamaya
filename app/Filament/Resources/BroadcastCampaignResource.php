<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastCampaignResource\Pages;
use App\Models\BroadcastCampaign;
use App\Models\Product;
use App\Services\Marketing\BroadcastSender;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BroadcastCampaignResource extends Resource
{
    protected static ?string $model = BroadcastCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Broadcast WA & Email';
    protected static ?string $modelLabel = 'Broadcast';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama kampanye')->required(),
            Forms\Components\Select::make('channel')->required()->options(['wa' => 'WhatsApp', 'email' => 'Email'])->live(),
            Forms\Components\TextInput::make('subject')->label('Subjek email')
                ->visible(fn (Forms\Get $get) => $get('channel') === 'email')
                ->required(fn (Forms\Get $get) => $get('channel') === 'email'),
            Forms\Components\Select::make('segment.type')->label('Segmen penerima')->required()->options(
                collect(['all_customers' => 'Semua customer', 'leads' => 'Leads terverifikasi'])
                    ->merge(Product::published()->get()->mapWithKeys(fn ($p) => ["buyers_of:{$p->id}" => "Pembeli: {$p->name}"]))
                    ->all()
            ),
            Forms\Components\TextInput::make('throttle_per_minute')->label('Kecepatan (pesan/menit)')
                ->numeric()->minValue(1)->maxValue(60)->default(20)
                ->helperText('WA: 10-20/menit aman dari banned. Email boleh lebih tinggi.'),
            Forms\Components\Textarea::make('body')->label('Isi pesan')->rows(8)->required()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('channel')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'sending' => 'warning', 'done' => 'success', 'paused' => 'gray', default => 'info',
                }),
                Tables\Columns\TextColumn::make('terkirim')->state(
                    fn (BroadcastCampaign $r) => $r->recipients()->where('status', 'sent')->count()
                        . '/' . $r->recipients()->count()
                ),
            ])
            ->actions([
                Tables\Actions\Action::make('kirim')->label('Mulai Kirim')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn (BroadcastCampaign $r) => in_array($r->status, ['draft', 'paused'], true))
                    ->requiresConfirmation()
                    ->modalDescription('Penerima diantrekan dan dikirim bertahap sesuai kecepatan yang diatur.')
                    ->action(function (BroadcastCampaign $record) {
                        $count = app(BroadcastSender::class)->queueRecipients($record);
                        Notification::make()->title("{$count} penerima diantrekan")
                            ->body('Pengiriman berjalan otomatis di latar belakang.')->success()->send();
                    }),
                Tables\Actions\Action::make('pause')->label('Jeda')->icon('heroicon-o-pause')->color('gray')
                    ->visible(fn (BroadcastCampaign $r) => $r->status === 'sending')
                    ->action(fn (BroadcastCampaign $record) => $record->update(['status' => 'paused'])),
                Tables\Actions\EditAction::make()
                    ->visible(fn (BroadcastCampaign $r) => $r->status === 'draft'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcastCampaigns::route('/'),
            'create' => Pages\CreateBroadcastCampaign::route('/create'),
            'edit' => Pages\EditBroadcastCampaign::route('/{record}/edit'),
        ];
    }
}
