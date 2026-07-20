<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\TaxSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** Settings: branding, provider notifikasi, rekening manual/QRIS, tax/PPN, Google OAuth, template checkout default. */
class StoreSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Settings & Branding';
    protected static ?string $title = 'Settings';
    protected static string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $tax = TaxSetting::current();
        $this->form->fill([
            'store_name' => jm_setting('store_name', config('app.name')),
            'accent_color' => jm_setting('accent_color', '#4f46e5'),
            'store_logo_path' => jm_setting('store_logo_path'),
            'store_favicon_path' => jm_setting('store_favicon_path'),
            'default_checkout_template' => jm_setting('default_checkout_template', 'standard'),
            'social_proof_enabled' => filter_var(jm_setting('social_proof_enabled', '1'), FILTER_VALIDATE_BOOL),
            'wa_provider' => jm_setting('wa_provider', 'fonnte'),
            'fonnte_token' => jm_setting('fonnte_token'),
            'email_provider' => jm_setting('email_provider', 'mailketing'),
            'mailketing_api_key' => jm_setting('mailketing_api_key'),
            'mail_from' => jm_setting('mail_from', env('MAIL_FROM_ADDRESS')),
            'google_client_id' => jm_setting('google_client_id'),
            'google_client_secret' => jm_setting('google_client_secret'),
            'manual_bank_name' => jm_setting('manual_bank_name'),
            'manual_account_number' => jm_setting('manual_account_number'),
            'manual_account_holder' => jm_setting('manual_account_holder'),
            'qris_image_url' => jm_setting('qris_image_url'),
            'tax_percentage' => (string) $tax->percentage,
            'tax_mode' => $tax->mode,
            'npwp' => $tax->npwp,
            'store_address' => $tax->store_address,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('Branding')->schema([
                    Forms\Components\TextInput::make('store_name')->label('Nama toko')->required(),
                    Forms\Components\ColorPicker::make('accent_color')->label('Warna aksen'),
                    Forms\Components\FileUpload::make('store_logo_path')->label('Logo')->image()->maxSize(1024)->disk('public')->directory('branding'),
                    Forms\Components\FileUpload::make('store_favicon_path')->label('Favicon')->image()->maxSize(512)->disk('public')->directory('branding'),
                    Forms\Components\Select::make('default_checkout_template')->label('Template checkout default')->options([
                        'minimal' => 'Minimal', 'sidebar-sticky' => 'Sidebar Sticky', 'standard' => 'Standard', 'two-step' => 'Two-Step',
                    ]),
                    Forms\Components\Toggle::make('social_proof_enabled')->label('Social proof bubble ("X baru saja membeli")')
                        ->helperText('Nama pembeli otomatis disamarkan (Bud****).'),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Notifikasi (WA & Email)')->schema([
                    Forms\Components\Select::make('wa_provider')->label('Provider WhatsApp')
                        ->options(['fonnte' => 'Fonnte (default)'])
                        ->helperText('OneSender / StarSender / Kirimdev tersedia di Tahap 3.'),
                    Forms\Components\TextInput::make('fonnte_token')->label('Fonnte Token')->password()->revealable(),
                    Forms\Components\Select::make('email_provider')->label('Provider Email')
                        ->options(['mailketing' => 'Mailketing (default)'])
                        ->helperText('DripSender tersedia di Tahap 3.'),
                    Forms\Components\TextInput::make('mailketing_api_key')->label('Mailketing API Key')->password()->revealable(),
                    Forms\Components\TextInput::make('mail_from')->label('Email pengirim')->email(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Transfer Manual & QRIS')->schema([
                    Forms\Components\TextInput::make('manual_bank_name')->label('Bank'),
                    Forms\Components\TextInput::make('manual_account_number')->label('No. rekening'),
                    Forms\Components\TextInput::make('manual_account_holder')->label('Atas nama'),
                    Forms\Components\TextInput::make('qris_image_url')->label('URL gambar QRIS')->url(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Google OAuth')->schema([
                    Forms\Components\TextInput::make('google_client_id')->label('Client ID'),
                    Forms\Components\TextInput::make('google_client_secret')->label('Client Secret')->password()->revealable(),
                    Forms\Components\Placeholder::make('hint')->content('Kosong = tombol "Masuk dengan Google" otomatis disembunyikan.'),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Pajak / PPN')->schema([
                    Forms\Components\TextInput::make('tax_percentage')->label('PPN (%)')->numeric()->minValue(0)->maxValue(100)->required(),
                    Forms\Components\Select::make('tax_mode')->label('Mode')->options([
                        'inclusive' => 'Inclusive (harga sudah termasuk PPN)',
                        'exclusive' => 'Exclusive (PPN ditambahkan saat checkout)',
                    ])->required(),
                    Forms\Components\TextInput::make('npwp')->label('NPWP (tampil di invoice)'),
                    Forms\Components\Textarea::make('store_address')->label('Alamat toko (tampil di invoice)'),
                    Forms\Components\Placeholder::make('note')->content('Perubahan hanya berlaku untuk order BARU. Order lama memakai snapshot pajak saat dibuat.'),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach (['store_name','accent_color','store_logo_path','store_favicon_path','default_checkout_template','social_proof_enabled',
                  'wa_provider','fonnte_token','email_provider','mailketing_api_key','mail_from',
                  'google_client_id','google_client_secret',
                  'manual_bank_name','manual_account_number','manual_account_holder','qris_image_url'] as $key) {
            Setting::put($key, $data[$key] ?? null);
        }
        TaxSetting::current()->update([
            'percentage' => (float) $data['tax_percentage'],
            'mode' => $data['tax_mode'],
            'npwp' => $data['npwp'],
            'store_address' => $data['store_address'],
        ]);
        ActivityLog::record('settings.updated');
        Notification::make()->title('Settings tersimpan')->success()->send();
    }

    public function testWa(): void
    {
        $svc = app(\App\Services\Notifications\NotificationService::class);
        $adapter = $svc->waProvider();
        if (! $adapter) { Notification::make()->title('Provider WA belum dikonfigurasi')->warning()->send(); return; }
        $result = $adapter->send(auth()->user()->phone ?? '', 'Test WA dari Javamaya ✓');
        Notification::make()->title($result['ok'] ? 'WA test terkirim ✓' : 'WA test gagal')
            ->body($result['error'] ?? 'Cek WhatsApp Anda.')->{$result['ok'] ? 'success' : 'danger'}()->send();
    }

    public function testEmail(): void
    {
        $svc = app(\App\Services\Notifications\NotificationService::class);
        $adapter = $svc->emailProvider();
        if (! $adapter) { Notification::make()->title('Provider email belum dikonfigurasi')->warning()->send(); return; }
        $result = $adapter->send(auth()->user()->email, 'Test email dari Javamaya ✓', 'Test Javamaya');
        Notification::make()->title($result['ok'] ? 'Email test terkirim ✓' : 'Email test gagal')
            ->body($result['error'] ?? 'Cek inbox Anda.')->{$result['ok'] ? 'success' : 'danger'}()->send();
    }
}
