<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['Tentang Kami', 'tentang', "## Tentang Toko Ini\n\nTulis profil toko/bisnis Anda di sini melalui Admin > Halaman Statis (CMS).", true],
            ['Syarat & Ketentuan', 'syarat-ketentuan', "## Syarat & Ketentuan\n\n1. Produk digital yang sudah dibeli tidak dapat dikembalikan kecuali dinyatakan lain.\n2. Akses akun bersifat pribadi dan tidak boleh dibagikan.\n3. Sesuaikan syarat lain sesuai bisnis Anda.", true],
            ['Kebijakan Privasi', 'privasi', "## Kebijakan Privasi\n\nKami menyimpan data nama, email, dan nomor WhatsApp untuk keperluan pengiriman produk dan notifikasi transaksi.\n\nAnda dapat meminta export atau penghapusan data melalui menu **Data Saya** di member area.", true],
            ['FAQ', 'faq', "## Pertanyaan Umum\n\n**Bagaimana cara mengakses produk setelah membayar?**\nAkses dikirim otomatis ke email & WhatsApp Anda, atau login ke member area.\n\n**Berapa lama pembayaran terkonfirmasi?**\nPembayaran otomatis terkonfirmasi dalam hitungan menit.", true],
            ['Panduan Update Manual', 'panduan-update-manual', "## Panduan Update Manual (Tahap 1)\n\n1. **Backup dulu** dari Admin > Backups > Backup Sekarang, unduh file .sql.\n2. Download ZIP versi baru dari Admin > Update Sistem.\n3. Buka cPanel > File Manager > folder aplikasi Anda.\n4. Upload ZIP lalu **Extract** (timpa file lama). **JANGAN hapus** folder `storage/` dan file `.env`.\n5. Kembali ke Admin > Update Sistem > klik **Selesai upload — Jalankan Migrasi & Finalisasi**.\n6. Selesai. Bila ada masalah, restore backup .sql via phpMyAdmin dan upload ulang ZIP versi lama.", false],
        ];
        foreach ($pages as [$title, $slug, $body, $footer]) {
            ContentPage::firstOrCreate(['slug' => $slug], [
                'title' => $title, 'body' => $body, 'published' => true, 'show_in_footer' => $footer,
            ]);
        }
    }
}
