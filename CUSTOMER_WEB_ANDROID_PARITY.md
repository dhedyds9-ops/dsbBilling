# MATRIKS PARITAS FITUR: WEB CUSTOMER PORTAL vs ANDROID CUSTOMER
## Dokumen Verifikasi Keselarasan Fungsional (Fase 1 & 2)

> **Prinsip Utama:** Satu dsBilling, Satu Database, Satu Aturan Bisnis, Dua Antarmuka Pelanggan (Web & Android).  
> **Status:** Terverifikasi 100% berdasarkan Kode Sumber Backend Aktual & Desain Mockup Aplikasi Android.  

---

## 1. Tabel Matriks Paritas Fungsional

| Fitur Utama | Web Customer Portal | Backend Domain Service | Android Customer App (Target) | Status Paritas |
|---|---|---|---|:---:|
| **Login Pelanggan** | `login-customer.blade.php` via `LoginRequest` (Multi-identifier: Code, Phone, Email, PPPoE) | `App\Models\User`, `App\Models\CRM\Customer`, auto-healing fallback | Layar Login Native, input fleksibel + toggle password + remember session | **Siap Dihubungkan** |
| **Penyimpanan Token & Sesi** | Web Cookie & Session Laravel | Sanctum Token (`createToken('customer-android')`) | `KeyStoreManager` (AES-256 GCM encrypted token storage) | **Siap Dihubungkan** |
| **Ganti Password Sesi Pertama** | Banner peringatan ganti password default di `customer-app.blade.php` | `CustomerProfileService::changePassword` | BottomSheet / Dialog wajib ganti sandi saat `needs_password_change = true` | **Siap Dihubungkan** |
| **Dashboard Pelanggan** | `App\Livewire\CustomerPortal\Dashboard` | `CustomerDashboardService::getDashboardData` | **Layar 1 Mockup:** Card Profil, ID Pelanggan, Status Online/Offline, Total Tagihan, Banner Promo | **Tersedia Penuh di Backend** |
| **Status Koneksi & Sinyal** | Menampilkan IP, ONU Model, RX Power dBm di Info Layanan | `CustomerService`, `Onu`, `GenieACSDriver` | Badge pill real-time status online/offline & redaman optik | **Tersedia Penuh di Backend** |
| **Daftar & Riwayat Tagihan** | `App\Livewire\CustomerPortal\Billing\InvoiceList` | `CustomerBillingService::getPaginatedInvoices` | **Layar 2 Mockup:** Card tagihan dengan status Belum Bayar / Sudah Bayar, invoice number, periode, nominal | **Tersedia Penuh di Backend** |
| **Detail Tagihan & Item** | `App\Livewire\CustomerPortal\Billing\InvoiceShow` | `CustomerBillingService::getInvoiceDetails` | BottomSheet Detail Tagihan: rincian paket, biaya administrasi, potongan/promo | **Tersedia Penuh di Backend** |
| **Pembayaran Online (Multi-Gateway)** | `InvoiceShow::pay` (Midtrans, Xendit, Tripay, Duitku, QRIS, VA) | `PaymentOrchestrationService::initiatePayment` | In-app QRIS display, Virtual Account copy-to-clipboard, WebView redirect jika diperlukan | **Tersedia Penuh di Backend** |
| **Konfirmasi Pembayaran Otomatis** | Asinkron via Webhook Gateway (`/api/payment/webhook/*`) | `PaymentWebhookController` + Dedup Transaction | Otomatis refresh status invoice setelah webhook gateway memverifikasi pembayaran | **Tersedia Penuh di Backend** |
| **Pengaturan Wi-Fi Mandiri (SSID & Sandi)** | `App\Livewire\CustomerPortal\SelfService\ChangeOnuWifiPassword` | `CustomerSelfServiceService::changeOnuWifiCredentials` | **Layar 3 Mockup:** Form ubah SSID, Password Wi-Fi, Mode Keamanan, Perangkat Terhubung, Simpan | **Tersedia Penuh di Backend** |
| **Daftar Host / Perangkat Terhubung** | `App\Livewire\CustomerPortal\SelfService\ConnectedDevices` | `CustomerSelfServiceService::getOnuConnectedDevices` | List host terhubung (nama perangkat, IP LAN, MAC address) dari GenieACS TR-069 | **Tersedia Penuh di Backend** |
| **Uji Kecepatan (Speedtest Realtime)** | `App\Livewire\CustomerPortal\SelfService\SpeedTest` | Server-based latency, ping, download & upload speed | Layar Speedtest Native dengan gauge animasi M3 dan pengujian latency/throughput | **Tersedia Penuh di Backend** |
| **Tiket Pengaduan & Lapor Gangguan** | `App\Livewire\CustomerPortal\Support\TicketCreate` | `CustomerSupportService::createTicket` | Form lapor gangguan: Judul, Kategori (Teknis/Billing), Prioritas, Deskripsi, Lampiran Foto | **Tersedia Penuh di Backend** |
| **Riwayat & Status Tiket Aduan** | `App\Livewire\CustomerPortal\Support\TicketList` | `CustomerSupportService::getPaginatedTickets` | List tiket aduan dengan timeline status (Open, In Progress, Selesai) | **Tersedia Penuh di Backend** |
| **Hubungi Admin / CS WhatsApp** | `App\Livewire\CustomerPortal\Support\ContactAdmin` | Setting `company.phone` via `Setting::getValue` | Tombol Quick Action langsung membuka obrolan WhatsApp resmi admin/CS | **Tersedia Penuh di Backend** |
| **Notifikasi Pelanggan** | `App\Livewire\CustomerPortal\Info` | `App\Models\Notification\Notification` (recipient `user_id`) | Tab Notifikasi / Pengumuman tagihan dan pemeliharaan jaringan | **Tersedia Penuh di Backend** |
| **Profil & Pengaturan Akun** | `App\Livewire\CustomerPortal\Profile\Profile` | `CustomerProfileService::updateProfile` | Layar Profil: nama, email, no WhatsApp, alamat pemasangan, ubah kata sandi | **Tersedia Penuh di Backend** |
| **Dark Mode & Light Mode** | Tailwind CSS `dark:` class via LocalStorage | CSS Tokens / Preferences | Material Design 3 `isSystemInDarkTheme()` / User Toggle Switch | **Siap Diimplementasikan** |

---

## 2. Kesimpulan Paritas

1. **Kelengkapan Fitur:** Seluruh fitur yang tampak pada mockup user (Layar 1: Dashboard & 6 Menu Cepat, Layar 2: Riwayat Tagihan, Layar 3: Pengaturan WiFi Mandiri) **100% didukung oleh arsitektur database dan service backend Laravel yang sudah ada**.
2. **Nol Duplikasi Bisnis:** Tidak diperlukan pembuatan model atau perhitungan bisnis baru. Seluruh logika tagihan, integrasi payment gateway, dan proxy TR-069 GenieACS tetap terpusat di backend Laravel.
3. **Kesiapan Antarmuka:** Android Customer hanya bertindak sebagai antarmuka client murni (Consumer API), memanggil controller API yang meneruskan request ke service terkait.
