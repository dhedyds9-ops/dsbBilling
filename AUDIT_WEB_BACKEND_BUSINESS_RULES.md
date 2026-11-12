# AUDIT WEB BACKEND BUSINESS RULES & SINGLE SOURCE OF TRUTH (SSOT)
**dsBilling Web Application (`D:\dsBilling`) & Web Portal Teknisi**  
**Dokumen Referensi Resmi Arsitektur & Otorisasi Backend**  
**Tanggal Audit:** 11 November 2026  
**Status:** READ-ONLY COMPREHENSIVE ARCHITECTURAL AUDIT  

---

## 1. PENDAHULUAN & PRINSIP TERTINGGI SISTEM

Aplikasi dsBilling beroperasi berdasarkan prinsip **Single Source of Truth (SSOT)** yang didefinisikan dalam `docs/SSOT.md` dan `.agents/AGENTS.md`.  
Laravel Backend (`D:\dsBilling`) merupakan satu-satunya penentu validitas, otorisasi, logika bisnis, dan integritas data bagi seluruh antarmuka (Web Backoffice, Web Portal Teknisi, dan Android Native Staff `D:\dsbilling-mobile`).

### Prinsip Utama Otorisasi:
1. **SSOT Supremacy:** Keputusan otorisasi mutlak berada di Backend. Klien (Web/Android) hanya merefleksikan kondisi akses dari backend.
2. **Role ≠ Permission:** Role menentukan *siapa pengguna* (jabatan fungsional), sedangkan Permission menentukan *apa yang boleh dilakukan* (granular capability).
3. **Role ≠ Branch:** Cabang (`branch_id`) adalah business/operational scope, bukan role.
4. **Presensi Aktif sebagai Gate Operasional:** Teknisi yang belum check-in dilarang mengeksekusi operasi lapangan.
5. **Lintas Cabang:** Teknisi dengan presensi aktif diizinkan mengerjakan penugasan (SPK/Tiket) lintas cabang jika pekerjaan tersebut ditugaskan kepadanya (`assigned_to == auth()->id()`). Kesamaan `branch_id` tidak boleh menjadi penghalang pekerjaan yang sah.

---

## 2. STRUKTUR DATABASE, SKEMA & SCOPING ELOQUENT

### 2.1. Isolasi Multi-Tenant & Cabang
- Skema database dsBilling menggunakan basis data relasional SQLite/MySQL dengan pembagian scope:
  - `company_id`: Isolasi tenant perusahaan tingkat tinggi.
  - `branch_id`: Isolasi cabang operasional kantor.
  - `reseller_id`: Isolasi bisnis mitra/reseller.
- Trait `HasBranchScope` menyematkan global scope pada model:
  - Query otomatis menyaring `branch_id = auth()->user()->branch_id`.
  - Jika pengguna adalah Administrator atau Manager Pusat, global scope dilepas.
- Trait `HasResellerScope` menyematkan global scope pada model pelanggan dan layanan:
  - Reseller hanya dapat mengakses data pelanggan miliknya (`reseller_id = auth()->user()->getEffectiveResellerId()`).

### 2.2. Pola Penanganan Query pada Web Portal Teknisi
Pemeriksaan pada `app/Livewire/Isp/Technician/MyJobs/Index.php` membuktikan:
```php
// Web Portal Teknisi melepaskan global scopes agar teknisi dapat melihat SPK yang ditugaskan kepadanya
$query = Ticket::withoutGlobalScopes()
    ->with(['customer', 'branch', 'assignedTo'])
    ->where(function ($q) use ($userId) {
        $q->where('assigned_to', $userId)
          ->orWhere('technician_id', $userId);
    });
```
**Aturan Resmi Web:** Web dsBilling **TIDAK** membatasi tiket teknisi berdasarkan kesamaan cabang teknisi dan cabang tiket. Selama tiket tersebut secara sah ditugaskan ke teknisi (`assigned_to = $user->id`), teknisi berhak melihat, mengambil, dan menyelesaikan pekerjaan tersebut.

---

## 3. ARSITEKTUR ROLE & PERMISSION (RBAC)

### 3.1. Definisi Enum `UserRole` (`app/Enums/UserRole.php`)
Sistem dsBilling mendefinisikan 10 role standar:
1. `administrator` — Akses penuh ke seluruh sistem ISP, konfigurasi, jaringan, dan keuangan.
2. `admin_gudang` — Staf logistik dan pergudangan; akses modul inventori, requisition, custody. Dilarang mengakses TR-069 dan keuangan.
3. `kasir` — Staf penagihan dan kasir; akses invoice, pembayaran, dan pencatatan kas.
4. `customer_service` — Staf layanan pelanggan; akses CRM, komplain, pembukaan tiket gangguan.
5. `noc` — Network Operations Center; pemantauan OLT, router core, alarm jaringan, dan TR-069 GenieACS.
6. `teknisi` — Staf teknisi lapangan; akses portal pekerjaan, instalasi PSB, perbaikan, diagnosa ONT, dan stok material tangan.
7. `marketing` — Staf promosi dan penjualan; prospek leads dan voucher hotspot.
8. `manager` — Supervisor/manajemen umum internal ISP.
9. `reseller` — Mitra bisnis/reseller; financial actor dengan hak akses khusus terhadap pelanggannya sendiri.
10. `customer` — End-user (pelanggan); dilarang login ke aplikasi staf internal.

### 3.2. Definisi Enum `UserPermission` (`app/Enums/UserPermission.php`)
Permission menggunakan format `{resource}.{action}`.  
Default permission untuk teknisi lapangan (`defaultTeknisiPermissions`):
- `technician.portal` — Akses portal teknisi
- `workforce.jobs.view` — Melihat daftar SPK
- `workforce.jobs.execute` — Mengeksekusi dan menyelesaikan SPK
- `workforce.qc.view` & `workforce.qc.execute` — Validasi kualitas instalasi
- `workforce.material.view` & `workforce.material.execute` — Pemakaian material
- `workforce.attendance.execute` — Absensi mandiri (Check-in/Check-out)
- `onu.view` — Melihat status ONT
- `onu.diagnose` — Diagnosa redaman dan sinyal
- `onu.configure.wifi` — Mengubah nama SSID dan password WiFi ONT
- `onu.configure.wan` — Konfigurasi PPPoE / IP WAN
- `onu.reboot` — Merestart modem ONT
- `inventory.custody`, `inventory.usage`, `inventory.return`, `inventory.requisition` — Pengelolaan stok di tangan

### 3.3. Logika Evaluasi Permission pada Model `User` (`app/Models/User.php`)
Metode `hasPermission(string $permission): bool`:
1. **Bypass Administrator:** Administrator selalu menghasilkan `true`.
2. **Direct Permissions:** Dicek pada tabel `permission_user` / Spatie.
3. **Pemeriksaan Berdasarkan Jabatan/Fungsi:**
   - **Teknisi:** Jika `isTechnician()` bernilai `true`, diizinkan menjalankan seluruh permission `defaultTeknisiPermissions()`, `technician.*`, `workforce.*`, `onu.view`, `onu.diagnose`, `onu.configure.wifi`, `onu.configure.wan`, `onu.reboot`.
   - **Gudang:** Khusus `inventory.*` dan `warehouse.*`.
   - **Kasir:** Khusus `invoice.*` dan `payment.*`.
   - **NOC:** Khusus `noc.*`, `monitoring.*`, `onu.*`.

---

## 4. KEBIJAKAN PRESENSI OPERASIONAL (ATTENDANCE ACCESS CONTROL)

### 4.1. Service Otorisasi: `AttendanceAuthorizationService`
File: `app/Services/Workforce/AttendanceAuthorizationService.php`

Aturan Evaluasi:
1. **Pengecualian Role:**
   - Administrator, Manager, Kasir, Customer Service, dan Reseller dibebaskan dari kewajiban absensi lapangan.
2. **Kewajiban Teknisi:**
   - Seluruh teknisi lapangan (`job_function = 'TECHNICIAN'` atau role `teknisi`) wajib memiliki record absensi aktif untuk hari operasional berjalan.
3. **Kriteria Presensi Aktif (`hasActiveAttendance`):**
   - Record berada pada tabel `attendances`.
   - `technician_id = auth()->id()`.
   - `date = Carbon::today()`.
   - `checked_in_at IS NOT NULL`.
   - `checked_out_at IS NULL`.
   - `status = AttendanceStatus::CHECKED_IN` (atau status aktif valid).
4. **Middleware Enforcement:**
   - Web: `Route::middleware(['workforce.checked_in'])` membungkus rute `/technician-portal/my-jobs`, `/technician-portal/swap-ont`, `/technician-portal/diagnostic`, `/technician-portal/dismantle`, `/technician-portal/installation/*`, `/inventory/*`.
   - API: `EnsureWorkforceCheckedIn` membungkus seluruh endpoint operasional di bawah `/api/mobile/*`.
   - Menghasilkan HTTP 403 dengan payload JSON:
     ```json
     {
       "success": false,
       "error_code": "ATTENDANCE_REQUIRED",
       "message": "Akses operasional ditolak. Anda belum melakukan absensi masuk (Check-in) hari ini."
     }
     ```

---

## 5. MODUL OPERASIONAL: WEB VS API

### 5.1. Modul Pelanggan (Customer Management)
- **Web:** Dikelola melalui Livewire `App\Livewire\Crm\Customer\Index`. Menyaring pelanggan berdasarkan cabang (`branch_id`) dan reseller (`reseller_id`).
- **Mobile API:** `MobileCustomerApiController::index`. Menampilkan daftar pelanggan dengan filter pencarian nama, kode, nomor HP, dan username PPPoE.

### 5.2. Modul SPK & Tiket (Work Order)
- **Web:** `App\Livewire\Isp\Technician\MyJobs\Index` dan `Tickets\Index`.
  - Teknisi melihat tiket yang ditugaskan kepadanya (`assigned_to = auth()->id()`).
  - Tiket unassigned dalam cabang atau wilayah kerja dapat diambil sendiri oleh teknisi.
- **Mobile API:** `MobileWorkforceApiController::getWorkOrders`.
  - Mengembalikan daftar tiket dan instruksi kerja aktif.
  - Mengizinkan aksi `start_progress` (mulai kerja) dan `complete` (selesai kerja dengan bukti foto/tanda tangan).

### 5.3. Modul Jaringan, ONT & GenieACS TR-069
- **Web:**
  - `Diagnostic\Index`: Diagnosa redaman RX/TX Power, status PON, uptime.
  - `Swap\Index`: Penggantian ONT rusak dengan ONT baru dari stok tangan teknisi.
  - `Dismantle\Index`: Pencabutan ONT dari pelanggan non-aktif dan pengembalian ke gudang.
- **Mobile API:**
  - `MobileNetworkApiController`: Swap ONT, Dismantle ONT, Provisioning Baru (PSB).
  - `MobileAcsProxyController`: Proxy aman ke GenieACS Port 7557. Melindungi perangkat dari akses tidak sah.
  - Operasi WiFi (`/api/mobile/acs/devices/{id}/wifi`): Memerlukan permission `onu.configure.wifi` atau `technician.portal`.

### 5.4. Modul Inventori & Material (Custody)
- **Web:** `App\Livewire\Inventory\Custody\Index` & `App\Livewire\Isp\Technician\Material\Index`.
  - Menampilkan material yang sedang dipegang oleh teknisi (kabel dropcore, fast connector, ONT).
- **Mobile API:** `MobileInventoryApiController::myStock`.
  - Menampilkan stok material tangan teknisi dan histori mutasi barang.

### 5.5. Modul Payroll & Keuangan
- **Web:** `App\Livewire\Isp\Technician\Payroll\Index` & `Show`.
  - Teknisi dapat melihat slip gaji bulanan, rincian insentif per SPK, tunjangan, dan potongan.
- **Mobile API:** `MobilePayrollApiController::index` & `show`.
  - Menampilkan ringkasan dan rincian slip gaji resmi staf.

---

## 6. KESIMPULAN AUDIT BACKEND
Logika bisnis Web dsBilling didesain dengan ketat mengutamakan:
1. Kontrol akses berbasis presensi aktif (`workforce.checked_in`).
2. Keleluasaan teknisi mengerjakan penugasan lintas cabang selama ada assignment resmi.
3. Pemisahan wewenang yang tegas antara staf gudang, kasir, teknisi, dan NOC.
4. Pengamanan hardware jaringan via backend proxy tanpa eksposur credential atau port mentah.
