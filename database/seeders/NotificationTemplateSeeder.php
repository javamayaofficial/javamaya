<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['order_created', 'wa', null,
             "Halo {name}! 🙏\nPesanan *{order_ref}* berhasil dibuat.\nTotal: *{total}*\n\nSelesaikan pembayaran di sini:\n{pay_url}"],
            ['order_created', 'email', 'Selesaikan pembayaran pesanan {order_ref}',
             "Halo {name},\n\nPesanan {order_ref} berhasil dibuat dengan total {total}.\nSelesaikan pembayaran melalui tautan berikut:\n{pay_url}\n\nTerima kasih!"],
            ['payment_confirmed', 'wa', null,
             "Pembayaran diterima! ✅\nPesanan *{order_ref}* — {total} LUNAS.\n\nAkses produk Anda:\n{member_url}\n\nInvoice PDF:\n{invoice_url}"],
            ['payment_confirmed', 'email', 'Pembayaran {order_ref} berhasil — akses Anda sudah aktif',
             "Halo {name},\n\nPembayaran pesanan {order_ref} sebesar {total} sudah kami terima.\n\nMasuk ke member area: {member_url}\nUnduh invoice: {invoice_url}\n\nSelamat belajar & terima kasih!"],
            ['access_expired', 'wa', null,
             "Halo {name}, masa akses *{product}* Anda telah berakhir. Perpanjang di sini agar tetap bisa mengakses:\n{renew_url}"],
            ['access_expired', 'email', 'Masa akses {product} berakhir',
             "Halo {name},\n\nMasa akses Anda untuk {product} telah berakhir.\nPerpanjang di sini: {renew_url}\n\nTerima kasih."],
            ['order_refunded', 'wa', null,
             "Halo {name}, refund pesanan *{order_ref}* sebesar {amount} telah diproses.\nAlasan: {reason}\nAkses produk terkait dinonaktifkan."],
            ['order_refunded', 'email', 'Refund pesanan {order_ref} diproses',
             "Halo {name},\n\nRefund pesanan {order_ref} sebesar {amount} telah diproses.\nAlasan: {reason}\n\nAkses produk dari pesanan ini dinonaktifkan."],
            ['certificate_issued', 'wa', null,
             "Selamat {name}! 🎓\nAnda menyelesaikan kelas *{class_name}*.\nKode sertifikat: {code}\nVerifikasi: {verify_url}"],
            ['certificate_issued', 'email', 'Sertifikat Anda untuk {class_name} 🎓',
             "Selamat {name}!\n\nAnda telah menyelesaikan kelas {class_name}.\nKode sertifikat: {code}\nVerifikasi publik: {verify_url}\n\nUnduh PDF dari member area Anda."],
            ['otp_code', 'wa', null,
             "Kode OTP Anda: *{code}*\nBerlaku {minutes} menit. JANGAN berikan kode ini ke siapa pun."],
            ['gdpr_export_ready', 'email', 'Export data Anda siap diunduh',
             "Halo {name},\n\nExport data Anda siap. Unduh dalam 24 jam:\n{download_url}"],
            ['abandoned_cart', 'wa', null,
             "Halo {name} 👋\nPesanan *{order_ref}* ({total}) masih menunggu pembayaran.\nSelesaikan sekarang sebelum kedaluwarsa:\n{pay_url}"],
            ['abandoned_cart', 'email', 'Pesanan {order_ref} menunggu pembayaran Anda',
             "Halo {name},\n\nPesanan {order_ref} sebesar {total} belum dibayar.\nSelesaikan di sini: {pay_url}\n\nAbaikan email ini jika sudah membayar."],
            ['waitlist_restock', 'wa', null,
             "Kabar baik, {name}! 🎉\n*{product}* yang Anda tunggu sudah TERSEDIA lagi.\nAmankan sekarang sebelum kehabisan:\n{url}"],
            ['waitlist_restock', 'email', '{product} tersedia lagi — khusus daftar tunggu',
             "Halo {name},\n\n{product} yang Anda tunggu sudah tersedia kembali.\nBeli sekarang: {url}"],
            ['lead_magnet_delivery', 'wa', null,
             "Halo {name}! 🎁\nIni akses *{title}* Anda:\n{url}\n\nSelamat menikmati!"],
            ['lead_magnet_delivery', 'email', 'Akses {title} Anda 🎁',
             "Halo {name},\n\nTerima kasih! Ini akses {title} Anda:\n{url}"],
        ];

        foreach ($templates as [$key, $channel, $subject, $body]) {
            NotificationTemplate::firstOrCreate(
                ['key' => $key, 'channel' => $channel],
                ['subject' => $subject, 'body' => $body]
            );
        }
    }
}
