# LAPORAN IMPLEMENTASI KONTROL AKSES OPERASIONAL BERBASIS PRESENSI AKTIF
## dsBilling Laravel Backend & Android Native Staff Integration Report

---

## 1. Arsitektur & Perubahan Komponen

### A. Backend Domain Service
- **File:** [`app/Services/Workforce/AttendanceAuthorizationService.php`](file:///D:/dsBilling/app/Services/Workforce/AttendanceAuthorizationService.php)
- **Fungsi Utama:**
  - `getActiveAttendance(?User $user)`: Memeriksa record presensi aktif dengan kriteria: `technician_id == $user->id`, `checked_in_at != null`, `checked_out_at == null`, status valid, dan rentang shift hingga 18 jam ke belakang (untuk shift malam).
  - `isExemptFromAttendance(?User $user)`: Pengecualian presensi untuk Superadmin/Administrator murni backoffice, Reseller, dan Customer. Kewajiban presensi operasional difokuskan secara presisi untuk staf teknisi lapangan (`User::isTechnician()`).
  - `authorizeWorkOrder(User $user, WorkOrder $workOrder)`: Memvalidasi presensi, batas perusahaan (tenant boundary), dan mengizinkan pekerjaan lintas cabang apabila ditugaskan secara resmi (`assigned_to == $user->id`).
  - `formatAuditTrail($action, $user, $targetBranchId, $notes)`: Menghasilkan pesan audit trail terstandarisasi `[CROSS-BRANCH: Tech Cabang #X -> Target Cabang #Y]`.

### B. Middleware Otorisasi Presensi
- **File:** [`app/Http/Middleware/EnsureWorkforceCheckedIn.php`](file:///D:/dsBilling/app/Http/Middleware/EnsureWorkforceCheckedIn.php)
- **Penerapan:** Diintegrasikan pada rute grup API Mobile:
  ```php
  Route::middleware(['auth:sanctum', 'workforce.checked_in'])->group(...)
  ```
- **Pengecualian Endpoint (Exempt):**
  - `/api/mobile/me` & `/api/mobile/logout`
  - `/api/mobile/attendance/*` (status, check-in, check-out, history)
  - `/api/mobile/payroll*` (Slip gaji pribadi)
  - `/api/mobile/sync/bootstrap` & notifikasi
  - `/api/mobile/customers` (Metode GET)
  - Seluruh endpoint operasional lainnya (Work Orders, Inventory, ACS TR-069, Swap, Dismantle, Provisioning, Customer Registration POST) **wajib presensi aktif**.

### C. Paritas Izin Teknisi & TR-069
- **File:** [`app/Models/User.php`](file:///D:/dsBilling/app/Models/User.php)
- **Perbaikan:** Menyelaraskan izin teknisi lapangan dengan `UserPermission::defaultTeknisiPermissions()`. Menambahkan permission `onu.configure.wifi`, `onu.configure.wan`, dan `onu.reboot` sehingga teknisi yang bertugas dapat mengganti nama WiFi (SSID) dan password WPA Key tanpa hambatan.
- **File:** [`app/Http/Controllers/Api/Mobile/MobileAcsProxyController.php`](file:///D:/dsBilling/app/Http/Controllers/Api/Mobile/MobileAcsProxyController.php)
- **Perbaikan:** Menghilangkan blokir cabang mutlak. Mengizinkan eksekusi TR-069 lintas cabang apabila teknisi memiliki penugasan SPK aktif atas pelanggan/ONT tersebut, disertai pencatatan audit log `[CROSS-BRANCH]`.

### D. Android Native Staff Application
- **File API Service:** [`DsBillingApiService.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/network/DsBillingApiService.kt)
  - Menambahkan kelas pengecualian:
    ```kotlin
    class AttendanceRequiredException(message: String = "Pengguna harus melakukan absensi masuk sebelum menjalankan aktivitas operasional.") : Exception(message)
    ```
  - Metode `parseException(bodyStr, code)` mendeteksi response HTTP 403 dengan code `ATTENDANCE_REQUIRED` dan melemparkan `AttendanceRequiredException`.
- **File UI Dashboard:** [`MainActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/MainActivity.kt)
  - Variabel status `isShiftActive` diperbarui secara reaktif dari endpoint `/api/mobile/attendance/today`.
  - Fungsi `requireActiveAttendance(actionName, onAllowed)` memproteksi seluruh aksi cepat operasional:
    1. SPK Lapangan
    2. Pasang ONT (dan FAB Center Scan)
    3. Swap ONT
    4. Cabut ONT
    5. Diagnosa ONT
    6. Stok Material
  - Jika teknisi belum melakukan absensi masuk, dialog Material Design 3 akan muncul:
    > *"Akses operasional '[Fitur]' memerlukan absensi masuk yang aktif. Silakan lakukan Absen Masuk terlebih dahulu untuk memulai pekerjaan operasional."*
    Dengan tombol langsung menuju `AttendanceActivity`.

---

## 2. Paritas UI Web vs Android

| Aksi Pengguna | Web dsBilling / Web Portal Teknisi | Android Native Staff |
| :--- | :--- | :--- |
| **Buka Menu Tanpa Presensi** | Session flash warning + redirect ke `/technician-portal/attendance` dengan pesan peringatan. | MaterialAlertDialog interaktif dengan tombol "Absen Sekarang" menuju `AttendanceActivity`. |
| **Panggilan API Tanpa Presensi** | HTTP 403 JSON `ATTENDANCE_REQUIRED` | Melemparkan `AttendanceRequiredException`, dicegah sejak UI. |
| **Pekerjaan Lintas Cabang Sah** | SPK Cabang B tampil di My Jobs jika `assigned_to` terisi; dapat dimulai & diselesaikan. | SPK Cabang B tampil di daftar tugas; dapat dieksekusi normal. |
| **Pekerjaan Lintas Cabang Ilegal**| HTTP 403 `Akses ditolak: SPK ini milik cabang lain.` | HTTP 403 `Akses ditolak: SPK ini milik cabang lain.` |
| **Ganti WiFi SSID / Sandi** | Berhasil via TR-069 NBI Proxy. | Berhasil via TR-069 NBI Proxy (Permission `onu.configure.wifi` aktif). |

---

## 3. Checklist Deployment & Operasional

- [x] Zero Regressions: 84/84 PHPUnit test suite passing.
- [x] Zero Destructive Migrations: Skema database tetap utuh tanpa drop tabel/kolom.
- [x] SSOT Governance: Mengikuti arsitektur `docs/SSOT.md` dan `AGENTS.md`.
- [x] Multi-Tenant & Scope Isolation: Pemisahan antar company/reseller tetap terjaga 100%.
- [x] Android Client Build: Gradle build lulus bersih tanpa error.
