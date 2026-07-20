<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Template Notifikasi';
    protected static ?string $modelLabel = 'Template';

    public static function canCreate(): bool { return false; }   // key baru datang dari rilis
    public static function canDelete($record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->disabled(),
            Forms\Components\TextInput::make('channel')->disabled(),
            Forms\Components\TextInput::make('subject')->label('Subjek (email)')
                ->visible(fn (?NotificationTemplate $record) => $record?->channel === 'email'),
            Forms\Components\Textarea::make('body')->label('Isi pesan')->rows(10)->required()
                ->helperText('Variabel: {name} {order_ref} {total} {pay_url} {invoice_url} {member_url} {product} {renew_url} {class_name} {code} {verify_url} {url} {title} {amount} {reason} {minutes} {download_url} — sesuai konteks template.'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('channel')->badge()
                    ->color(fn ($state) => $state === 'wa' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('body')->limit(60),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
