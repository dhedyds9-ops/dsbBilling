# AUDIT MENYELURUH SISTEM BACKEND & DATABASE CUSTOMER DSBILLING
## Dokumen Hasil Audit Read-Only (Fase 1)

> **Status:** Terverifikasi dari Kode Sumber Aktual & Skema Database  
> **Tanggal Audit:** 11 November 2026  
> **Arsitektur Referensi:** Single Source of Truth (`docs/SSOT.md`)  
> **Workspace Web & API:** `D:\dsBilling` (Laravel 12, SQLite Multi-Tenant, Sanctum)  

---

## 1. Audit Skema Database (Database Actual)

Berdasarkan pembacaan langsung skema SQLite pada `D:\dsBilling`, berikut adalah tabel-tabel aktual yang menjadi fondasi modul Pelanggan:

### A. Tabel `customers`
* **Lokasi Model:** [`App\Models\CRM\Customer`](file:///D:/dsBilling/app/Models/CRM/Customer.php)
* **Kolom Aktual:**
  `id`, `user_id`, `code`, `reseller_id`, `branch_id`, `name`, `phone`, `email`, `status`, `address`, `latitude`, `longitude`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`.
* **Karakteristik & Scopes:**
  * Menggunakan `SoftDeletes`.
  * Menggunakan trait `HasResellerScope` (`reseller_id`) dan `HasBranchScope` (`branch_id`).
  * Relasi `user_id` menghubungkan entitas CRM Customer dengan akun login di tabel `users`.
  * Event `booted`:
    * Saat `creating`: Jika `code` kosong, otomatis di-generate oleh `CustomerCodeGenerator`.
    * Saat `deleted`: Menonaktifkan `user` terkait (`is_active = false`) dan soft-delete relasi `customerServices`.
    * Saat `restoring`: Mengaktifkan kembali `user` terkait dan restore `customerServices`.
    * Saat `saved`: Menyelaraskan `reseller_id` ke semua `customer_services`, `pppoe_users`, dan `hotspot_users`.

### B. Tabel `users`
* **Lokasi Model:** [`App\Models\User`](file:///D:/dsBilling/app/Models/User.php)
* **Kolom Aktual:**
  `id`, `uuid`, `name`, `email`, `email_verified_at`, `password`, `whatsapp`, `customer_code`, `pppoe_username`, `onu_sn`, `is_active`, `username`, `avatar`, `identity_number`, `address`, `balance`, `branch_id`, `job_function`, `job_title`, `reseller_id`, `password_changed_at`, `remember_token`, `created_at`, `updated_at`.
* **Peran Pelanggan:**
  * Menggunakan Spatie Permission / HasRoles: Role `customer`.
  * Kolom `customer_code`: Menyimpan kode identitas pelanggan (contoh: `CUST-001` atau format tanggal `20261101...`).
  * Kolom `whatsapp`: Nomor WhatsApp aktif yang dapat digunakan untuk login dan kontak admin.
  * Kolom `password_changed_at`: Menandai apakah pengguna sudah mengganti password default pertama kali (`123456`).

### C. Tabel `customer_services`
* **Lokasi Model:** [`App\Models\Customer\CustomerService`](file:///D:/dsBilling/app/Models/Customer/CustomerService.php)
* **Kolom Aktual:**
  `id`, `uuid`, `customer_id`, `contract_id`, `service_id`, `service_profile_id`, `onu_id`, `username`, `password`, `status`, `optical_status`, `tr069_status`, `service_status`, `diagnostic_status`, `last_seen_at`, `activated_at`, `suspended_at`, `reseller_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`.
* **Fungsi:**
  * Penghubung antara akun Pelanggan (`customer_id`) dengan Paket Layanan (`service_profile_id`) dan Modem ONT (`onu_id`).
  * Menyimpan kredensial autentikasi PPPoE/Hotspot (`username` & `password`).
  * Menyimpan status operasional dan redaman optik.

### D. Tabel `invoices` & `invoice_items`
* **Lokasi Model:** [`App\Models\Billing\Invoice`](file:///D:/dsBilling/app/Models/Billing/Invoice.php)
* **Kolom `invoices`:**
  `id`, `uuid`, `customer_id`, `contract_id`, `invoice_number`, `issue_date`, `due_date`, `total_amount`, `paid_amount`, `currency`, `status`, `item_details`, `branch_id`, `reseller_id`, `grace_period_until`, `created_at`, `updated_at`.
* **Kolom `invoice_items`:**
  `id`, `uuid`, `invoice_id`, `description`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`.
* **Status Invoice:** `unpaid`, `partial`, `paid`, `cancelled`.
* **Aturan Bisnis:** Tagihan hanya dianggap lunas (`status = 'paid'`) jika `paid_amount >= total_amount` atau setelah webhook payment gateway sukses diterima backend.

### E. Tabel `payments`
* **Lokasi Model:** [`App\Models\Payment\Payment`](file:///D:/dsBilling/app/Models/Payment/Payment.php)
* **Kolom Aktual:**
  `id`, `uuid`, `customer_id`, `amount`, `currency`, `method`, `status`, `reference_number`, `paid_at`, `gateway`, `gateway_transaction_id`, `branch_id`, `reseller_id`, `created_at`, `updated_at`.
* **Status Payment:** `pending`, `success`, `failed`, `expired`.

### F. Tabel `service_profiles` (Paket Layanan)
* **Lokasi Model:** [`App\Models\ISP\ServiceProfile`](file:///D:/dsBilling/app/Models/ISP/ServiceProfile.php)
* **Kolom Kunci:**
  `id`, `code`, `name`, `service_type` (pppoe, hotspot), `download_speed`, `upload_speed`, `price`, `base_price`, `validity_days`, `status`, `fup_enabled`, `fup_threshold`, `branch_id`, `reseller_id`.

### G. Tabel `tickets` (Pengaduan & Gangguan)
* **Lokasi Model:** [`App\Models\Support\Ticket`](file:///D:/dsBilling/app/Models/Support/Ticket.php)
* **Kolom Aktual:**
  `id`, `uuid`, `customer_id`, `title`, `description`, `category`, `priority`, `status`, `assigned_to`, `reseller_id`, `resolved_at`, `due_date`, `photos` (JSON array), `resolution_notes`, `created_at`, `updated_at`.
* **Kategori Valid:** `Teknis`, `Billing`, `Layanan`, `Lainnya`.
* **Prioritas Valid:** `low`, `medium`, `high`, `critical`.
* **Status:** `open`, `in_progress`, `resolved`, `closed`.

### H. Tabel `onus` (Modem Pelanggan)
* **Lokasi Model:** [`App\Models\ISP\Onu`](file:///D:/dsBilling/app/Models/ISP/Onu.php)
* **Kolom Kunci:**
  `id`, `olt_id`, `odp_id`, `model`, `serial_number`, `wifi_ssid`, `wifi_password`, `admin_password`, `rx_power_dbm`, `tx_power_dbm`, `provision_status`, `last_seen_at`.

### I. Tabel `notifications`
* **Lokasi Model:** [`App\Models\Notification\Notification`](file:///D:/dsBilling/app/Models/Notification/Notification.php)
* **Kolom Aktual:**
  `id`, `uuid`, `recipient_id`, `title`, `message`, `type`, `status`, `data` (JSON), `created_at`, `updated_at`.

---

## 2. Audit Domain Services Backend (CustomerPortal Services)

Sistem Web dsBilling telah memiliki arsitektur service terisolasi di folder [`app/Services/CustomerPortal/`](file:///D:/dsBilling/app/Services/CustomerPortal/):

1. **`CustomerDashboardService`** ([`app/Services/CustomerPortal/CustomerDashboardService.php`](file:///D:/dsBilling/app/Services/CustomerPortal/CustomerDashboardService.php)):
   * Mengumpulkan ringkasan status pelanggan: `next_billing_date`, `primary_service`, `service_profile`, `internet_status` (Online/Offline/Isolir via FreeRADIUS/GenieACS), `active_invoices`, `total_outstanding`, dan statistik pemakaian bandwidth bulanan (`usage_stats`).
   * Memiliki mekanisme *auto-healing*: jika relasi `customer_services` belum terikat ke `pppoe_users`/`hotspot_users`, service otomatis menghubungkannya tanpa error.

2. **`CustomerBillingService`** ([`app/Services/CustomerPortal/CustomerBillingService.php`](file:///D:/dsBilling/app/Services/CustomerPortal/CustomerBillingService.php)):
   * `getPaginatedInvoices(int $customerId, int $perPage)`: Mengambil daftar tagihan terurut jatuh tempo dengan strict scoping `customer_id`.
   * `getInvoiceDetails(int $customerId, int $invoiceId)`: Mengambil rincian invoice, item, dan riwayat pembayaran parsial dengan validasi kepemilikan anti-IDOR.
   * `getPaymentHistory(int $customerId, int $perPage)`: Riwayat transaksi pembayaran pelanggan.

3. **`CustomerSelfServiceService`** ([`app/Services/CustomerPortal/CustomerSelfServiceService.php`](file:///D:/dsBilling/app/Services/CustomerPortal/CustomerSelfServiceService.php)):
   * `getOnuWifiCredentials(customerId, onuId)`: Mengambil nama SSID dan password live dari GenieACS TR-069.
   * `getOnuConnectedDevices(customerId, onuId)`: Mengambil daftar perangkat host yang tersambung ke modem Wi-Fi.
   * `changeOnuWifiCredentials(customerId, onuId, newSsid, newPassword, ...)`: Mengubah nama WiFi dan kata sandi pelanggan secara real-time via TR-069 NBI Proxy dengan validasi hak milik.

4. **`CustomerSupportService`** ([`app/Services/CustomerPortal/CustomerSupportService.php`](file:///D:/dsBilling/app/Services/CustomerPortal/CustomerSupportService.php)):
   * `createTicket(customerId, data)`: Mendaftarkan tiket aduan gangguan pelanggan baru dan memicu `TicketCreatedEvent`.
   * `getPaginatedTickets(customerId, perPage)`: Mengambil daftar tiket milik pelanggan aktif.
   * `getTicketDetails(customerId, ticketId)`: Mengambil detail tiket dan catatan penanganan teknisi.

5. **`CustomerProfileService`** ([`app/Services/CustomerPortal/CustomerProfileService.php`](file:///D:/dsBilling/app/Services/CustomerPortal/CustomerProfileService.php)):
   * `getProfile(userId)`: Mengambil data akun pengguna.
   * `updateProfile(userId, data)`: Memperbarui nama, email, dan no HP dengan validasi backend.
   * `changePassword(userId, currentPassword, newPassword)`: Memverifikasi hash password saat ini sebelum memperbarui ke password baru.

6. **`PaymentOrchestrationService`** ([`app/Services/Adapters/Payment/PaymentOrchestrationService.php`](file:///D:/dsBilling/app/Services/Adapters/Payment/PaymentOrchestrationService.php)):
   * Mendukung Midtrans, Xendit, Tripay, Duitku, Transfer Bank Manual, dan E-Wallet Manual.
   * Menghasilkan Snap Token, URL pembayaran, QRIS String, atau Nomor Virtual Account secara tersentralisasi.
   * Verifikasi mutasi dan status pembayaran dilakukan secara asinkron via Webhook resmi ber-signature (`/api/payment/webhook/{driver}/{signatureToken}`).

---

## 3. Audit Customer Portal Web (Livewire Components)

Customer Portal Web saat ini berjalan dengan rute prefix `/customer-portal` dan dilindungi middleware `['role:customer']`:
* **Dashboard:** [`App\Livewire\CustomerPortal\Dashboard`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Dashboard.php)
* **Daftar Tagihan:** [`App\Livewire\CustomerPortal\Billing\InvoiceList`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Billing/InvoiceList.php)
* **Detail & Bayar Tagihan:** [`App\Livewire\CustomerPortal\Billing\InvoiceShow`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Billing/InvoiceShow.php)
* **Pengaturan Wi-Fi Mandiri:** [`App\Livewire\CustomerPortal\SelfService\ChangeOnuWifiPassword`](file:///D:/dsBilling/app/Livewire/CustomerPortal/SelfService/ChangeOnuWifiPassword.php)
* **Perangkat Terhubung:** [`App\Livewire\CustomerPortal\SelfService\ConnectedDevices`](file:///D:/dsBilling/app/Livewire/CustomerPortal/SelfService/ConnectedDevices.php)
* **Speedtest Realtime:** [`App\Livewire\CustomerPortal\SelfService\SpeedTest`](file:///D:/dsBilling/app/Livewire/CustomerPortal/SelfService/SpeedTest.php)
* **Tiket Pengaduan:** [`App\Livewire\CustomerPortal\Support\TicketList`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Support/TicketList.php) & [`TicketCreate`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Support/TicketCreate.php)
* **Hubungi Admin:** [`App\Livewire\CustomerPortal\Support\ContactAdmin`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Support/ContactAdmin.php) (integrasi nomor WhatsApp perusahaan dari `settings`)
* **Notifikasi / Info:** [`App\Livewire\CustomerPortal\Info`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Info.php)
* **Profil Pelanggan:** [`App\Livewire\CustomerPortal\Profile\Profile`](file:///D:/dsBilling/app/Livewire/CustomerPortal/Profile/Profile.php)

---

## 4. Audit Autentikasi & Multi-Identifier Login

Pada [`app/Http/Requests/Auth/LoginRequest.php`](file:///D:/dsBilling/app/Http/Requests/Auth/LoginRequest.php), sistem mendukung login pelanggan fleksibel tanpa mengorbankan keamanan:
1. **Identifier yang Diterima:**
   * ID Pelanggan / Customer Code (contoh: `2026101607`, `CUST-001`).
   * Username akun pengguna.
   * Alamat Email.
   * Nomor WhatsApp / Telepon (dengan normalisasi otomatis format `08...` dan `628...`).
   * Username PPPoE / Hotspot pelanggan.
2. **Auto-Heal Akun User:**
   * Jika profil CRM Customer ada namun akun `User` belum terbuat, sistem secara otomatis membuatkan akun `User` dengan role `customer` dan menghubungkan `user_id`.
3. **Verifikasi Password Cerdas:**
   * Mencocokkan Hash Bcrypt.
   * Fallback verifikasi terhadap password layanan PPPoE pelanggan atau password default `123456` pada login pertama kali.
   * Mewajibkan pergantian password jika `password_changed_at` masih `null`.
