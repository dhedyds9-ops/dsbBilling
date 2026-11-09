# AUDIT LAPORAN: HALAMAN ABSENSI & AKSES ROLE DSBILLING
**Status:** Audit Komprehensif (Read-Only)  
**Dokumen Referensi:** `docs/SSOT.md`, `.agents/rules/`, `.agents/skills/`  
**Target Repositori:**  
- Web Portal: `D:\dsBilling` (Laravel 12, Livewire 3/4, Tailwind CSS)  
- Mobile App: `D:\dsbilling-mobile` (Android Native Kotlin, Material Design 3)  
**Tanggal Audit:** 09 November 2026  

---

## 1. PENYEBAB UTAMA MASALAH (AKAR MASALAH SCREENSHOT)

Pada aplikasi Android `dsbilling-mobile`, akun teknisi (**Irpan Candra Kurniawan**) menampilkan status card:
> *"Belum Shift — Belum check-in shift hari ini — [MASUK]"*

Ketika tombol **[MASUK]** pada card shift atau tombol Quick Action **"Absensi Shift"** ditekan, muncul dialog pop-up:
```text
🕒 Absensi & Shift Kerja
Status Hari Ini (2026-11-09):
🔴 Belum Absen Masuk Hari Ini

Catatan: Wajib absen masuk sebelum memulai transaksi/pekerjaan lapangan.
```
Namun di dalam dialog **tidak ada tombol atau opsi apapun untuk melakukan Check-In maupun Check-Out**. Mengetuk layar di luar dialog hanya menutup pop-up tersebut tanpa aksi apapun.

### A. Root Cause 1: Konflik Fatal UI pada `MaterialAlertDialogBuilder` (Android)
Di dalam [`MainActivity.kt`](file:///d:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/MainActivity.kt) baris 752–773:
```kotlin
MaterialAlertDialogBuilder(this@MainActivity)
    .setTitle("🕒 Absensi & Shift Kerja")
    .setMessage("Status Hari Ini (${data.date ?: '-'}):\n$statusText\n\nCatatan: Wajib absen masuk sebelum memulai transaksi/pekerjaan lapangan.")
    .setItems(options.toTypedArray()) { _, which ->
        when (options[which]) {
            "✅ Absen Masuk (Check-In Shift)" -> { ... }
            "🚪 Absen Pulang (Check-Out Shift)" -> { ... }
        }
    }
    .show()
```
**Mekanisme Bug di Android SDK:**  
Pada implementasi `AlertController` bawaan Android (yang dibungkus oleh Material Components):
- Jika method `.setMessage()` dipanggil bersamaan dengan `.setItems()`, komponen **`mMessageView` akan mengambil alih tampilan dan secara otomatis menyembunyikan/menonaktifkan `mListView`**.
- Karena builder tidak melampirkan `.setPositiveButton()` atau `.setNegativeButton()`, AlertDialog tersebut dirender menjadi **kotak teks murni yang statis tanpa daftar pilihan yang bisa diklik**.
- Inilah penyebab visual 100% persis seperti pada tangkapan layar pengguna.

### B. Root Cause 2: Ketiadaan Halaman / Layar Khusus Absensi di Mobile
Aplikasi Android sama sekali tidak memiliki Activity atau BottomSheet khusus absensi (misalnya `AttendanceActivity` atau `AttendanceBottomSheet`). Fitur absensi yang sangat krusial hanya "dititipkan" ke dalam dialog alert darurat yang rusak.

### C. Root Cause 3: Koordinat GPS Hardcoded `(0.0, 0.0)`
Di dalam callback `MainActivity.kt` baris 759 & 766 (seandainya tombol dapat ditekan):
```kotlin
val inRes = api.checkInAttendance(0.0, 0.0, "Absen masuk via HP", true)
val outRes = api.checkOutAttendance(0.0, 0.0, "Shift selesai")
```
Aplikasi mengirim koordinat palsu/dummy `(0.0, 0.0)` (titik nol Samudra Atlantik) tanpa meminta koordinat aktual dari perangkat melalui `FusedLocationProviderClient` atau `LocationManager`, serta tanpa pemeriksaan runtime permission `ACCESS_FINE_LOCATION`.

---

## 2. KONDISI ARSITEKTUR BACKEND (`D:\dsBilling`)

### A. Skema Basis Data & Model `Attendance`
- **Tabel:** `attendances` (Migration: `2026_07_01_000008_create_attendances_table.php`)
- **Struktur Kolom:**
  - `id` (UUID, Primary Key)
  - `technician_id` (UUID, Foreign Key ke tabel `users.id`) — *Catatan:* Penamaan kolom menggunakan `technician_id`, namun diisi oleh ID seluruh staf operasional.
  - `date` (Date, format `YYYY-MM-DD`)
  - `status` (`AttendanceStatus` Enum: `absent`, `checked_in`, `checked_out`, `late`, `sick`, `permission`)
  - `check_in_latitude` & `check_in_longitude` (Double)
  - `checked_in_at` (Timestamp)
  - `check_out_latitude` & `check_out_longitude` (Double)
  - `checked_out_at` (Timestamp)
  - `notes` (Text)
- **Ketiadaan Tabel Shift/Jadwal Kerja:**  
  Tidak ada tabel `shifts` atau `work_schedules`. Jam shift dan ambang keterlambatan di-hardcode secara parsial di beberapa controller yang saling bertentangan:
  - `MobileAttendanceApiController.php`: batas telat **08:30 WIB**.
  - `AttendancePrompt.php` (Livewire Modal): batas telat **08:30 WIB**.
  - `Technician/Attendance/Index.php` (Web): batas telat **09:00 WIB**.
  - `NOC/AttendanceWidget.php` (Web Widget): batas telat **09:00 WIB**.

### B. Domain Service vs Bypass Langsung
- Backend memiliki class Domain DDD: [`app/Services/Workforce/AttendanceService.php`](file:///d:/dsBilling/app/Services/Workforce/AttendanceService.php) dan [`Src/Domain/Workforce/Attendance.php`](file:///d:/dsBilling/Src/Domain/Workforce/Attendance.php) dengan value object `GPSCoordinate`.
- **Anomali Arsitektur:** Seluruh controller dan komponen Livewire (`MobileAttendanceApiController`, `AttendancePrompt`, `Technician/Attendance/Index`, `NOC/AttendanceWidget`) **mem-bypass `AttendanceService`** dan langsung melakukan `Attendance::create()` atau `$attendance->update()`. Logika domain menjadi tidak terpusat.

### C. Backend Mobile API (`MobileAttendanceApiController.php`)
Endpoints terdaftar di `routes/api.php`:
1. `GET /api/mobile/attendance/today` — Mengembalikan data presensi hari ini.
2. `POST /api/mobile/attendance/check-in` — Menerima `latitude`, `longitude`, `notes`, `is_field_assignment`.
3. `POST /api/mobile/attendance/check-out` — Menerima `latitude`, `longitude`, `notes`.
- **Kelemahan API:**
  - Tidak ada validasi geofence di sisi API (menerima angka berapapun termasuk `0.0, 0.0`).
  - Tidak ada endpoint riwayat absensi (`/api/mobile/attendance/history` atau `/summary`).
  - Tidak ada endpoint informasi koordinat cabang kantor (`/api/mobile/attendance/geofence`).
  - Belum mendukung upload bukti foto/selfie presensi.

### D. Middleware `EnsureWorkforceCheckedIn` (Attendance Gate)
File: [`app/Http/Middleware/EnsureWorkforceCheckedIn.php`](file:///d:/dsBilling/app/Http/Middleware/EnsureWorkforceCheckedIn.php)
- **Role yang Terkena Gate:** `technician`, `admin_gudang`, `noc`, `kasir`, `customer_service`, serta user dengan `job_function` / `job_title` terkait.
- **Role Dikecualikan:** `reseller`, `customer`, serta `administrator`/`superadmin` yang murni bertindak sebagai pengawas (oversight).
- **Mekanisme Proteksi:**
  - Jika belum absen masuk hari ini, semua request HTTP mutasi (`POST`, `PUT`, `PATCH`, `DELETE`) **dihambat (HTTP 403 / redirect back dengan pesan error)**.
  - Untuk teknisi yang mengakses `technician-portal/*`, otomatis dialihkan ke `route('technician.attendance')`.
  - Mengirim session flash: `must_check_in_popup = true`.

---

## 3. KONDISI WEB PORTAL DSBILLING

| Portal / Role | Halaman Khusus Absensi | Mekanisme Presensi yang Ada | Masalah yang Teridentifikasi |
| :--- | :--- | :--- | :--- |
| **Teknisi Lapangan** (`technician`) | Ada (`/technician-portal/attendance`) | Halaman Livewire mandiri lengkap: Geofencing multi-cabang (150m), deteksi tugas luar, time-lock checkout 15 menit, riwayat 7 hari. | Berfungsi baik di Web browser, namun teknisi di lapangan menggunakan Android App yang dialognya rusak. |
| **NOC Operator** (`noc`) | **Tidak Ada** | Header Widget (`AttendanceWidget`) pada layout `noc.blade.php`. Catat IP, tanpa validasi GPS. | Tidak ada halaman riwayat atau menu sidebar di `MenuRegistry`. |
| **Admin Gudang** (`admin_gudang`, `warehouse`) | **Tidak Ada** | Mengandalkan modal pop-up `AttendancePrompt`. | Menu sidebar "Absensi Mandiri" salah link ke `admin.attendance.index` (rekap admin) dan tersembunyi karena permission hilang. |
| **Kasir / Billing** (`kasir`) | **Tidak Ada** | Mengandalkan modal pop-up `AttendancePrompt`. | Tidak ada halaman mandiri maupun menu khusus di sidebar. |
| **Customer Service** (`customer_service`) | **Tidak Ada** | Mengandalkan modal pop-up `AttendancePrompt`. | Tidak ada halaman mandiri maupun menu khusus di sidebar. |
| **Sales / Marketing** (`marketing`) | **Tidak Ada** | Mengandalkan modal pop-up `AttendancePrompt`. | Tidak ada halaman mandiri. |
| **Manager / Admin** (`manager`, `admin`) | **Tidak Ada** (hanya tabel rekap) | Route `/admin/attendance` (`admin.attendance.index`) adalah **tabel monitoring seluruh staf**, bukan form presensi diri sendiri. | Manager yang bertugas operasional tidak memiliki tombol absen masuk untuk dirinya sendiri. |
| **Reseller** (`reseller`) | *Dikecualikan* | Tidak wajib absensi (Mitra B2B). | Sesuai SSOT bisnis. |

### Masalah Layout Modal Web (`AttendancePrompt.php`):
Komponen modal `<livewire:workforce.attendance-prompt />` **hanya di-include pada satu layout: `resources/views/layouts/enterprise.blade.php` baris 185**.  
Layout lain seperti `admin.blade.php`, `noc.blade.php`, `app.blade.php`, dan `technician.blade.php` **tidak memiliki komponen modal ini**. Akibatnya, jika staf membuka halaman dengan layout tersebut saat belum absen, request mutasi mereka terblokir oleh middleware tanpa adanya modal presensi yang muncul! Selain itu, `AttendancePrompt.php` **hanya memiliki fungsi Check-In dan tidak memiliki tombol Check-Out**.

---

## 4. KONDISI APLIKASI ANDROID (`D:\dsbilling-mobile`)

### A. Tampilan Dashboard Utama (`MainActivity.kt`)
1. **Shift Card:**
   - Card `cardShiftStatus` ditampilkan untuk role: `TECHNICIAN`, `WAREHOUSE`, `ADMIN_GUDANG`, `NOC`, `MANAGER`, `ADMINISTRATOR`.
   - Menampilkan badge "Belum Shift" / "Sedang Bertugas" dan tombol `btnShiftAction` ("MASUK" / "PULANG").
   - Keduanya hanya mengarah ke `showAttendanceDialog()` yang mengalami bug layout.
2. **Quick Actions:**
   - Untuk Teknisi: Tombol Quick Action 2 berlabel "Absensi Shift" (`btnQuickAction2`) mengarah ke `showAttendanceDialog()`.
   - Untuk Gudang/NOC: Tombol Quick Action `btnActionAttendance` mengarah ke `showAttendanceDialog()`.
3. **Bottom Navigation (`bottom_nav_menu.xml`):**
   - Berisi 5 item: Beranda, SPK, Material, Perangkat, Lainnya.
   - **Tidak ada tab Absensi**. Staf tidak bisa mengakses absensi selain dari card/quick action yang rusak tersebut.
4. **Ketiadaan Fitur GPS Native:**
   - Modul Android tidak mengimplementasikan `FusedLocationProviderClient` untuk mengambil latitude dan longitude perangkat saat presensi.
   - AndroidManifest mungkin memiliki permission, namun runtime permission check (`ActivityCompat.requestPermissions`) tidak dijalankan di alur absensi.

---

## 5. MATRIKS ROLE VS AKSES ABSENSI DSBILLING

| Role Pengguna | Gate Presensi (`EnsureWorkforceCheckedIn`) | Akses Web Portal | Akses Android Mobile | Metode Validasi Lokasi Target | Status Keterlambatan |
| :--- | :---: | :--- | :--- | :--- | :---: |
| **Teknisi Lapangan** | **Wajib** | Halaman Penuh (`/technician-portal/attendance`) | Card & Quick Action (Dialog rusak) | GPS Geofence Multi-Cabang (150m) / Tugas Luar | > 09:00 WIB |
| **Admin Gudang** | **Wajib** | Modal Pop-up (hanya di `enterprise.blade.php`) | Card & Quick Action (Dialog rusak) | Geofence Kantor / IP Jaringan Kantor | > 08:30 WIB |
| **NOC Operator** | **Wajib** | Widget Header (`AttendanceWidget`) | Card & Quick Action (Dialog rusak) | IP Whitelist Kantor / Jaringan NOC | > 09:00 WIB |
| **Kasir / Billing** | **Wajib** | Modal Pop-up (hanya di `enterprise.blade.php`) | - | IP Whitelist Kantor Kasir | > 08:30 WIB |
| **Customer Service**| **Wajib** | Modal Pop-up (hanya di `enterprise.blade.php`) | - | IP Whitelist Kantor / VPN | > 08:30 WIB |
| **Sales Lapangan** | **Wajib** | Modal Pop-up (hanya di `enterprise.blade.php`) | - | GPS Geofence / Mode Lapangan | > 08:30 WIB |
| **Manager Cabang** | Opsional (Jika operasional) | Tabel Rekap (`admin.attendance.index`) | Card & Quick Action (Dialog rusak) | Geofence / Bypass Pengawas | > 09:00 WIB |
| **Administrator** | **Bypass** (Kecuali merangkap) | Tabel Rekap Seluruh Cabang | Dialog (jika role operasional) | Bebas / Pengawas Sistem | - |
| **Reseller** | **Bebas** (Mitra) | Tidak Ada | Dihilangkan (`View.GONE`) | Tidak Berlaku | - |
| **Customer** | **Bebas** (Pelanggan) | Tidak Ada | Tidak Ada | Tidak Berlaku | - |

---

## 6. DAFTAR ROUTE & ENDPOINT

### A. Route Web Existing
- `GET /technician-portal/attendance` (`technician.attendance`) — Halaman mandiri teknisi.
- `GET /attendance` (`admin.attendance.index`) — Tabel rekapitulasi presensi admin/manager.
- *Hilang/Tidak Ada:* Route mandiri untuk staf kantor (Gudang, NOC, CS, Kasir).

### B. Endpoint API Existing
- `GET /api/mobile/attendance/today` — Status presensi hari ini.
- `POST /api/mobile/attendance/check-in` — Check-in via mobile.
- `POST /api/mobile/attendance/check-out` — Check-out via mobile.
- *Hilang/Kurang:*
  - `GET /api/mobile/attendance/history` — Riwayat presensi staf (7-30 hari terakhir).
  - `GET /api/mobile/attendance/geofence` — Data koordinat kantor cabang aktif untuk validasi jarak pada peta mobile.
  - `POST /api/mobile/attendance/upload-photo` — Bukti foto presensi (opsional/selfie).

---

## 7. DAFTAR KOMPONEN YANG TERPUTUS (DISCONNECTED / DEFECTS)

1. **Defek Android UI (`MainActivity.kt:752`):**  
   Penggunaan `.setMessage()` dan `.setItems()` secara simultan menghilangkan menu opsi tombol presensi.
2. **Defek Koordinat Dummy Mobile (`MainActivity.kt:759, 766`):**  
   Pengiriman angka statis `0.0, 0.0` ke endpoint API.
3. **Ketiadaan Layar Mandiri Mobile:**  
   Tidak ada `AttendanceBottomSheet` atau `AttendanceActivity` dengan visual peta / indikator jarak meter ke kantor cabang.
4. **Defek Permission Staf Gudang & NOC (`UserPermission.php`):**  
   `defaultGudangPermissions()` dan `defaultNocPermissions()` **tidak menyertakan permission `workforce.attendance.execute`**. Akibatnya menu "Absensi Mandiri" di gudang terfilter keluar dan hilang dari sidebar.
5. **Defek Routing Menu Gudang (`MenuRegistry.php:116`):**  
   Menu "Absensi Mandiri" di Gudang justru diarahkan ke `admin.attendance.index` (rekap admin) dan bukan modul presensi diri sendiri.
6. **Defek Layout Injeksi Modal Web:**  
   `<livewire:workforce.attendance-prompt />` tidak di-include di layout selain `enterprise.blade.php`.
7. **Ketiadaan Fitur Check-Out di Modal Web (`AttendancePrompt.php`):**  
   Modal hanya menyediakan `checkIn()`, tidak memiliki implementasi `checkOut()`.
8. **Inkonsistensi Batas Waktu Keterlambatan:**  
   Ada yang menggunakan `08:30 WIB` dan ada yang menggunakan `09:00 WIB` tanpa konfigurasi terpusat.
9. **Bypass Domain Layer:**  
   `AttendanceService.php` (DDD) tidak digunakan oleh endpoint manapun.

---

## 8. DAMPAK RISIKO OPERASIONAL & PAYROLL

1. **Pemotongan Gaji Otomatis (Financial Loss Karyawan):**  
   Pada modul penggajian [`app/Livewire/Admin/Payroll/Generate.php`](file:///d:/dsBilling/app/Livewire/Admin/Payroll/Generate.php) baris 108:
   ```php
   $absentPenalty = (float) \App\Models\Setting::getValue('payroll.absent_penalty', 100000);
   ```
   Sistem membebankan penalti **Rp 100.000 per hari absen**. Karena teknisi dan staf tidak dapat menekan tombol absen di aplikasi, sistem mencatat status mereka sebagai `absent`, yang mengakibatkan pemotongan gaji secara tidak adil dan memicu ketidakpuasan staf lapangan.
2. **Kebuntuan Operasional (Deadlock Transaksi):**  
   Middleware `EnsureWorkforceCheckedIn` memblokir pengeluaran barang di gudang (`inventory/*`), penutupan tiket SPK, dan pembayaran kasir jika staf belum absen. Ketika antarmuka absensi tidak bisa ditekan, staf terkunci total dari pekerjaannya.
3. **Risiko Keamanan Integritas Data (Spoofing):**  
   Endpoint API menerima koordinat `0.0, 0.0` tanpa penolakan. Staf nakal berpotensi melakukan manipulasi request curl/Postman dari rumah tanpa benar-benar hadir di lokasi kantor/lapangan.

---

## 9. REKOMENDASI TAHAPAN PERBAIKAN (PHASED REMEDIATION)

### Tahap 1: Hotfix Android Mobile (Mendesak)
1. Ganti dialog `MaterialAlertDialogBuilder` yang rusak dengan **`AttendanceBottomSheet`** (Material 3 BottomSheet) yang menampilkan:
   - Status shift hari ini (Jam masuk, jam keluar, durasi kerja).
   - Tombol tegas berukuran penuh: **[Absen Masuk (Check-In)]** dan **[Absen Pulang (Check-Out)]**.
   - Input catatan tugas luar jika berada di luar jangkauan kantor.
2. Integrasikan integrasi `FusedLocationProviderClient` Android untuk membaca koordinat GPS perangkat secara riil sebelum memanggil API `/check-in` dan `/check-out`.

### Tahap 2: Standardisasi Backend API & Geofencing
1. Tambahkan validasi radius geofence di `MobileAttendanceApiController` (paritas dengan logika Haversine multi-cabang yang sudah ada di Web).
2. Tambahkan endpoint `GET /api/mobile/attendance/history` agar staf dapat memantau rekaman kehadirannya dalam 30 hari terakhir langsung dari aplikasi mobile.
3. Berikan opsi flag `is_field_assignment = true` dengan catatan wajib jika teknisi langsung bertugas di rumah pelanggan.

### Tahap 3: Pembenahan Web Portal & Role Permission
1. Tambahkan permission `workforce.attendance.execute` ke dalam `defaultGudangPermissions()`, `defaultNocPermissions()`, dan role staf terkait di `UserPermission.php`.
2. Pasang modul presensi mandiri atau include modal `<livewire:workforce.attendance-prompt />` ke seluruh layout staf operasional (`layouts/noc.blade.php`, `layouts/admin.blade.php`).
3. Lengkapi `AttendancePrompt.php` dengan tombol dan logika **Check-Out Shift**.
4. Perbaiki menu sidebar Gudang di `MenuRegistry.php` agar membuka antarmuka presensi mandiri, bukan rekap admin.

### Tahap 4: Sentralisasi Konfigurasi Jam Kerja & Penggajian
1. Buat pengaturan dinamis di database / `Setting` untuk jam masuk shift dan toleransi keterlambatan (misal `workforce.shift_start`, `workforce.late_threshold`) agar konsisten di seluruh Web dan Mobile.
2. Sambungkan kembali alur presensi ke `AttendanceService.php` guna menjaga kebersihan domain dan SSOT arsitektur dsBilling.

---

*Laporan audit ini disusun dalam mode READ-ONLY tanpa modifikasi kode sumber. Siap dilanjutkan ke tahap implementasi perbaikan setelah mendapatkan persetujuan.*
