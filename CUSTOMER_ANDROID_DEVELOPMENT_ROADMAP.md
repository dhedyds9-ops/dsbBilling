# ROADMAP PENGEMBANGAN APLIKASI ANDROID CUSTOMER DSBILLING
## Rencana Aksi Rekayasa Bertahap (Fase 1 hingga Fase 7)

> **Status Saat Ini:** Fase 1 (Audit Read-Only) & Fase 2 (Dokumentasi Desain & Matriks Paritas) Selesai.  
> **Tahap Selanjutnya:** Fase 3 (Persetujuan Arsitektur & Pelaporan ke Pengguna).  

---

## 1. Tahapan Pengembangan Terstruktur

```text
┌──────────────────────┐
│  FASE 1: AUDIT       │  [SELESAI]
│  Audit Database,     │  • Skema SQLite 100% dipetakan
│  Backend, Web Portal │  • Services domain customer diinventarisir
└──────────┬───────────┘
           │
┌──────────▼───────────┐
│  FASE 2: DESAIN      │  [SELESAI]
│  Spesifikasi API,    │  • 5 Dokumen SSOT telah disusun
│  Paritas & Arsitektur│  • Matriks keselarasan Web vs Android 100% cocok
└──────────┬───────────┘
           │
┌──────────▼───────────┐
│  FASE 3: PERSETUJUAN │  [AKTIF SEKARANG]
│  Review & Feedback   │  • Paparan hasil audit komprehensif ke pengguna
│  Konfirmasi Struktur │  • Menunggu konfirmasi sebelum membuat kode/proyek
└──────────┬───────────┘
           │
┌──────────▼───────────┐
│  FASE 4: BACKEND API │  [NEXT STEP SETELAH PERSETUJUAN]
│  Expose Controller   │  • Buat `CustomerApiController.php` di Laravel
│  /api/customer/*     │  • Hubungkan ke `CustomerPortalServices` yang sudah ada
└──────────┬───────────┘  • Unit Test API (Sanctum, Ownership, Anti-IDOR)
           │
┌──────────▼───────────┐
│  FASE 5: ANDROID DEV │  [NEXT STEP]
│  Project Init & UI   │  • Inisialisasi project Android di `D:\dsbilling-customer`
│  Jetpack Compose M3  │  • Implementasi Screen Dashboard, Tagihan, WiFi, Tiket
└──────────┬───────────┘  • Integrasi Retrofit & KeyStoreManager
           │
┌──────────▼───────────┐
│  FASE 6: PENGUJIAN   │  [TESTING & QUALITY GATES]
│  Parity & E2E Test   │  • Validasi kecocokan data Web vs Android (Akun sama)
│  Security Audit      │  • Uji anti-IDOR antar akun pelanggan
└──────────┬───────────┘  • Uji offline caching & handling koneksi lambat
           │
┌──────────▼───────────┐
│  FASE 7: RILIS & OPS │  [PRODUCTION READINESS]
│  Build Release APK   │  • Kompilasi signed release APK (`dsbilling-customer.apk`)
│  Dokumentasi Rilis   │  • Upload ke server lokal / link unduh portal
└──────────────────────┘
```

---

## 2. Rincian Eksekusi & Kriteria Sukses Per Milestone

### Milestone 1: Pembangunan Customer API di Laravel Backend (Fase 4)
* **Tugas:**
  1. Mendaftarkan rute grup `/api/customer/*` pada [`routes/api.php`](file:///D:/dsBilling/routes/api.php).
  2. Membuat [`app/Http/Controllers/Api/Customer/CustomerApiController.php`](file:///D:/dsBilling/app/Http/Controllers/Api/Customer/CustomerApiController.php) yang memanggil domain service:
     * `CustomerDashboardService`
     * `CustomerBillingService`
     * `CustomerSelfServiceService`
     * `CustomerSupportService`
     * `CustomerProfileService`
     * `PaymentOrchestrationService`
  3. Mengaktifkan Sanctum token generation untuk pengguna ber-role `customer`.
* **Kriteria Keberhasilan:** Seluruh endpoint terverifikasi via test suite PHPUnit (`CustomerApiSecurityTest.php`) dengan 100% assertions lulus.

---

### Milestone 2: Inisialisasi Project Android Customer (Fase 5)
* **Tugas:**
  1. Membuat struktur project Android Native di direktori `D:\dsbilling-customer`.
  2. Mengonfigurasi `build.gradle.kts` dengan dependencies:
     * Jetpack Compose (BOM) & Material 3
     * Retrofit 2 + OkHttp 4 + Gson
     * AndroidX Security Crypto (EncryptedSharedPreferences)
     * Coil Compose (Async Image Loading)
  3. Membangun Core Network & Interceptor (Auth Token, Base URL, Error Handler).
* **Kriteria Keberhasilan:** Project terkompilasi bersih tanpa dependensi bermasalah.

---

### Milestone 3: Implementasi UI Sesuai Mockup BillingHub (Fase 5)
* **Tugas:**
  1. **Layar 1 (Dashboard):** Header ISP, Theme Switcher, Search Bar, Banner Promo, Card Profil & ID Pelanggan, Status Koneksi Realtime, Kartu Total Tagihan & Jatuh Tempo, 6 Tombol Quick Actions (*Tagihan, Tiket Aduan, Paket Internet, Pengaturan WiFi, Test Speed, Hubungi Admin*), Bottom Navigation Bar (*Home, Informasi, Profile, Logout*).
  2. **Layar 2 (Riwayat Tagihan):** List card invoice (Status Belum Bayar / Sudah Bayar, invoice number, periode, nominal, metode pembayaran, tombol bayar / dialog QRIS).
  3. **Layar 3 (Pengaturan Wi-Fi):** Status Wi-Fi, Form ubah SSID, Form ubah Password Wi-Fi, Pilihan Keamanan (WPA2/WPA3), Card Perangkat Terhubung, Tombol Simpan Perubahan.
  4. **Layar Tambahan:** Speedtest Realtime, Form Lapor Gangguan + Lampirkan Foto, Informasi Kontak CS WhatsApp.
* **Kriteria Keberhasilan:** Tampilan visual presisi mengikuti referensi mockup yang diunggah, responsif pada mode Gelap dan Terang.

---

### Milestone 4: Pengujian Paritas & Rilis (Fase 6 & 7)
* **Tugas:**
  1. Menguji login menggunakan akun pelanggan riil di database.
  2. Membandingkan keselarasan data antara Web Customer Portal dan Android Customer (ID pelanggan, nominal tagihan, status koneksi, nama WiFi).
  3. Membangun release APK `app-release.apk` dan menyediakannya di server dsBilling.
* **Kriteria Keberhasilan:** Paritas 100% cocok, zero security bypass, build APK siap pakai.
