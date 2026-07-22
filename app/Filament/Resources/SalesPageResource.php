<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesPageResource\Pages;
use App\Models\CanvasPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesPageResource extends Resource
{
    protected static ?string $model = CanvasPage::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Sales Page';
    protected static ?string $modelLabel = 'Sales Page';
    protected static ?string $pluralModelLabel = 'Sales Pages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Konten halaman')->schema([
                Forms\Components\TextInput::make('title')->label('Judul halaman')->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, ?CanvasPage $record) {
                        if (! $record) $set('slug', str($state)->slug());
                    }),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)
                    ->helperText(fn (?CanvasPage $record) => 'URL: ' . url('/l/' . ($record?->slug ?: '{slug}'))),
                Forms\Components\Textarea::make('content_html')->label('HTML Sales Page')
                    ->required()->columnSpanFull()
                    ->rows(18)
                    ->extraAttributes([
                        'style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;',
                        'spellcheck' => 'false',
                    ])
                    ->helperText('Tempel HTML jadi dari luar di sini. Bisa isi section/body saja atau satu dokumen HTML lengkap (`<!DOCTYPE html>...`). Jika dokumen lengkap, sistem akan mengambil isi `<head>`, `<body>`, `title`, `meta description`, `body class`, dan `body style` secara otomatis.'),
                Forms\Components\Textarea::make('embed_html')->label('Embed HTML tambahan (opsional / legacy)')
                    ->rows(4)->dehydrated(false)->columnSpanFull()
                    ->placeholder('<iframe src="https://youtube.com/embed/..."></iframe>')
                    ->helperText('Opsional. Jika Anda sudah menaruh semua kode di kolom HTML utama, kolom ini tidak perlu diisi.')
                    ->afterStateHydrated(function (Forms\Components\Textarea $component, ?CanvasPage $record) {
                        // Tampilkan kembali embed yang tersimpan di dalam penanda
                        if ($record && preg_match('/<!--EMBED-->(.*?)<!--\/EMBED-->/s', (string) $record->content_html, $m)) {
                            $component->state(trim($m[1]));
                        }
                    }),
            ]),

            Forms\Components\Section::make('Publikasi & penempatan')->schema([
                Forms\Components\Toggle::make('published')->label('Terbitkan')->default(false)
                    ->helperText('Halaman hanya bisa diakses publik bila terbit.'),
                Forms\Components\Toggle::make('is_homepage')->label('Jadikan halaman utama (homepage)')
                    ->helperText('Bila aktif, halaman ini menggantikan katalog default di domain utama "/". Otomatis hanya satu yang bisa jadi homepage.')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $set('show_header', false);
                            $set('show_footer', false);
                        }
                    }),
                Forms\Components\Placeholder::make('url_preview')->label('Akan tampil di')
                    ->content(fn (Forms\Get $get) => $get('is_homepage')
                        ? '🏠 Halaman utama (/) — menggantikan katalog dan tampil full canvas'
                        : '🔗 ' . url('/l/' . ($get('slug') ?: '{slug}'))),
            ])->columns(2),

            Forms\Components\Section::make('Tampilan & SEO')->collapsed()->schema([
                Forms\Components\Toggle::make('show_header')->label('Tampilkan header toko')->default(true),
                Forms\Components\Toggle::make('show_footer')->label('Tampilkan footer toko')->default(true),
                Forms\Components\TextInput::make('meta_title')->label('Meta title (SEO)')
                    ->maxLength(70)->placeholder('Kosong = pakai judul halaman'),
                Forms\Components\Textarea::make('meta_desc')->label('Meta description (SEO)')
                    ->maxLength(160)->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold'),
                Tables\Columns\IconColumn::make('is_homepage')->label('Homepage')->boolean()
                    ->trueIcon('heroicon-s-home')->falseIcon('heroicon-o-minus')->trueColor('success'),
                Tables\Columns\TextColumn::make('slug')->label('URL')
                    ->formatStateUsing(fn (CanvasPage $r) => $r->is_homepage ? '/' : '/l/' . $r->slug)
                    ->copyable(),
                Tables\Columns\IconColumn::make('published')->label('Terbit')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')->label('Lihat')->icon('heroicon-o-eye')
                    ->color('gray')->visible(fn (CanvasPage $r) => $r->published)
                    ->url(fn (CanvasPage $r) => $r->is_homepage ? url('/') : url('/l/' . $r->slug), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /** Gabungkan embed_html ke content_html dalam penanda <!--EMBED-->...<!--/EMBED-->. */
    public static function mergeEmbed(array $data, ?string $embed): array
    {
        $html = $data['content_html'] ?? '';
        // buang blok embed lama
        $html = preg_replace('/<!--EMBED-->.*?<!--\/EMBED-->/s', '', (string) $html);
        $html = rtrim($html);
        if (filled($embed)) {
            $html .= "\n<!--EMBED-->\n<div class=\"jm-embed my-8\">" . $embed . "</div>\n<!--/EMBED-->";
        }
        $data['content_html'] = $html;
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesPages::route('/'),
            'create' => Pages\CreateSalesPage::route('/create'),
            'edit' => Pages\EditSalesPage::route('/{record}/edit'),
        ];
    }
}
