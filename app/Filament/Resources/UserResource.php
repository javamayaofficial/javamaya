<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';

    public const STAFF_PERMISSIONS = [
        'products.manage', 'orders.read', 'orders.confirm', 'refunds.manage',
        'classes.manage', 'coupons.manage', 'content.manage', 'reports.read',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')->label('WhatsApp'),
            Forms\Components\Select::make('role')->required()->options([
                'customer' => 'Customer', 'staff' => 'Staff', 'super_admin' => 'Super Admin',
            ])->live()
              ->disabled(fn () => ! auth()->user()->isSuperAdmin())
              ->helperText('Hanya Super Admin yang bisa mengubah role.'),
            Forms\Components\CheckboxList::make('permissions')->label('Izin staff')
                ->options(array_combine(self::STAFF_PERMISSIONS, self::STAFF_PERMISSIONS))
                ->visible(fn (Forms\Get $get) => $get('role') === 'staff')
                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?User $record) {
                    $component->state($record?->staffPermissions()->pluck('permission')->all() ?? []);
                })
                ->dehydrated(false),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->helperText('Kosongkan untuk tidak mengubah password.'),
            Forms\Components\Toggle::make('is_affiliate')->label('Affiliate (Tahap 2)'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('role')->badge()->color(fn ($state) => match ($state) {
                'super_admin' => 'danger', 'staff' => 'warning', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('role')->options([
                'customer' => 'Customer', 'staff' => 'Staff', 'super_admin' => 'Super Admin',
            ]),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
