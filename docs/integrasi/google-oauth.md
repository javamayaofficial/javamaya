# Integrasi Google OAuth (Login dengan Google)

1. Buka https://console.cloud.google.com → buat Project.
2. **APIs & Services → OAuth consent screen** → External → isi nama aplikasi & domain.
3. **Credentials → Create Credentials → OAuth client ID** → Web application:
   - Authorized redirect URI: `https://domainanda.com/auth/google/callback`
4. Salin **Client ID** + **Client Secret** ke Javamaya: Admin → Settings → tab **Google OAuth** → Simpan.

Perilaku: kredensial kosong → tombol "Masuk dengan Google" otomatis tersembunyi di halaman login (tidak error). Email Google yang sama dengan akun existing otomatis tertaut.
