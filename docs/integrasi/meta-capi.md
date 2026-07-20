# Meta Pixel & Conversions API (CAPI)

**Tahap 1**: Meta Pixel browser-side sudah didukung — tambahkan Pixel ID lewat tabel `tracking_pixels` (provider `meta`) dan PageView terpasang otomatis di semua halaman publik.

**Tahap 2 (menyusul)**: server-side **Purchase event** via Conversions API. Slot `.env` sudah tersedia:
```
META_PIXEL_ID=
META_ACCESS_TOKEN=
META_TEST_EVENT_CODE=
```
Purchase akan dikirim dari OrderPaidPipeline (dedup dengan event_id = order_ref) sehingga tracking tetap akurat meski browser memblokir pixel.
