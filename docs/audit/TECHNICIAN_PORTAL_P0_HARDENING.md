# LAPORAN IMPLEMENTASI P0 HARDENING — PORTAL TEKNISI
## dsBilling Enterprise

**Tanggal:** 2026-11-05  
**Status:** SELESAI (100% PASS)  
**Tingkat Prioritas:** P0 Hardening (Security & Data Integrity)  

---

## 1. RINGKASAN EKSEKUTIF

Berdasarkan hasil audit independen pada `docs/audit/TECHNICIAN_PORTAL_P0_VERIFICATION.md`, telah dilakukan pengerasan keamanan dan integritas data tingkat P0 pada 3 area kritis:
1. **P0-H1 — Private Storage untuk Bukti Tiket dan Pekerjaan**
2. **P0-H2 — Payroll Branch Isolation untuk Manager**
3. **P0-H3 — Concurrency Lock & Unique Constraint untuk ODP dan ONU**

Seluruh 8 skenario pengujian wajib telah dijalankan melalui suite otomatis dan dinyatakan **100% LULUS (PASS)** tanpa regresi pada portal Customer maupun portal NOC.

---

## 2. DETAIL PERUBAHAN IMPLEMENTASI

### 2.1 P0-H1: Private Storage & Authenticated Media Stream
- **Controller Baru:** `app/Http/Controllers/Isp/Technician/TicketPhotoController.php`
  - Mengalirkan file foto bukti secara aman menggunakan `response()->stream()` (chunked stream).
  - Sanitasi direktori traversal menggunakan `basename()`.
  - Otorisasi berlapis:
    - Autentikasi wajib (`auth()->check()`, unauthenticated -> HTTP 401).
    - Administrator / SuperAdmin: Akses penuh.
    - Teknisi: Wajib berada pada branch yang sama DAN merupakan teknisi yang ditugaskan pada tiket tersebut (atau pembuat tiket). Akses lintas branch -> HTTP 403.
    - Fallback backward compatibility: Mendukung membaca file legacy pada disk `public` (`tickets/...`) secara transparan tanpa mengubah URL di database historis.
- **Route Baru:** `routes/web.php`
  - `GET /technician-portal/tickets/{ticket}/photos/{filename}` (`technician.tickets.photo`), di bawah grup middleware `['auth']`.
- **Livewire Components:**
  - `app/Livewire/Isp/Technician/Tickets/Index.php`: Menyimpan upload baru ke private disk `local` (`tickets/{id}/photo.jpg`) dan menghasilkan URL rute terlindungi.
  - `app/Livewire/Isp/Technician/MyJobs/Index.php`: Mengarahkan upload baru ke private storage.
  - `resources/views/livewire/isp/technician/tickets/index.blade.php`: Menyesuaikan resolver gambar untuk mendukung rute private dan legacy storage.
  - `resources/views/livewire/isp/technician/my-jobs/index.blade.php`: Menyesuaikan resolver gambar.

### 2.2 P0-H2: Payroll Branch Isolation
- **Komponen:** `app/Livewire/Isp/Technician/Payroll/Show.php`
  - Mengetatkan otorisasi pada method `mount()`:
    - Teknisi hanya dapat melihat slip gajinya sendiri (`$user->employee->id === $payroll->employee_id`).
    - Manager dibatasi secara ketat hanya dapat melihat slip gaji karyawan yang berada di cabang yang sama (`$user->branch_id === $payroll->employee->branch_id`). Akses lintas cabang -> HTTP 403 Forbidden.
    - Administrator dan SuperAdmin mempertahankan visibilitas audit lintas cabang.

### 2.3 P0-H3: Concurrency Lock & Unique Constraint (ODP & ONU)
- **Audit Pra-Migrasi:**
  - Audit terhadap tabel `onus` menunjukkan **0 duplikasi Serial Number** sebelum migrasi diaplikasikan.
- **Migration Baru:** `database/migrations/2026_11_05_223000_add_unique_constraint_to_onus_serial_number.php`
  - Menambahkan unique index tingkat database: `onus_serial_number_unique` pada kolom `serial_number`.
  - Berhasil dimigrasikan via `php artisan migrate`.
- **Wizard PSB:** `app/Livewire/Isp/Technician/Installation/Wizard.php`
  - Menerapkan `DB::transaction()` dengan pessimistic locking:
    - `Odp::where('id', $this->odp_id)->lockForUpdate()->first()`: Memeriksa sisa port (`availablePortCount > 0`) dan menginkremen `used_port_count` secara atomik. Jika port habis, transaksi dibatalkan dengan `DomainException`.
    - `Onu::withoutGlobalScopes()->where('serial_number', $sn)->lockForUpdate()->first()`: Memastikan tidak ada race condition registrasi ONU antar cabang.
    - Menangkap `UniqueConstraintViolationException` dan `DomainException` dengan pesan flash yang aman bagi teknisi.
- **Audit Form:** `app/Livewire/Isp/Technician/Audit/Form.php`
  - Menambahkan `withoutGlobalScopes()` pada validasi ONU dan CustomerService agar konflik lintas branch tidak lolos dari deteksi.

---

## 3. HASIL PENGUJIAN OTOMATIS (8 SKENARIO WAJIB)

Pengujian dieksekusi melalui script `scratch/test_hardening.php`:

| No | Skenario Pengujian | Hasil Aktual | Status |
|---|---|---|---|
| 1 | Akses unauthenticated ke foto private pekerjaan | HTTP 401 Unauthorized | **PASS** |
| 2 | Akses teknisi lintas branch ke foto tiket | HTTP 403 Forbidden | **PASS** |
| 3 | Akses Manager lintas branch ke slip gaji payroll | HTTP 403 Forbidden | **PASS** |
| 4 | Akses Administrator global ke payroll & foto | HTTP 200 OK | **PASS** |
| 5 | Concurrency lock kapasitas ODP (port = 0) | Process 2 diblokir dengan pesan valid | **PASS** |
| 6 | Duplikasi Serial Number ONU | Diblokir oleh DB Unique Constraint | **PASS** |
| 7 | Backward compatibility foto legacy di disk public | HTTP 200 OK via controller terotentikasi | **PASS** |
| 8 | Regresi rute portal Customer dan portal NOC | Seluruh rute customer dan NOC utuh | **PASS** |

---

## 4. PROSEDUR ROLLBACK

Jika sewaktu-waktu diperlukan pemulihan:
1. **Rollback Migration Unique Index:**
   ```bash
   php artisan migrate:rollback --step=1
   ```
2. **Restore Perubahan Kode:**
   ```bash
   git checkout main -- app/Http/Controllers/Isp/Technician/TicketPhotoController.php
   git checkout main -- app/Livewire/Isp/Technician/Tickets/Index.php
   git checkout main -- app/Livewire/Isp/Technician/MyJobs/Index.php
   git checkout main -- app/Livewire/Isp/Technician/Payroll/Show.php
   git checkout main -- app/Livewire/Isp/Technician/Installation/Wizard.php
   git checkout main -- app/Livewire/Isp/Technician/Audit/Form.php
   git checkout main -- routes/web.php
   ```

---

## 5. KESIMPULAN & REKOMENDASI

Seluruh kriteria penerimaan P0 Hardening telah dipenuhi dengan sempurna. Sistem kini memiliki perlindungan privasi file bukti, isolasi cabang untuk data penggajian, serta perlindungan konkurensi database pada perangkat jaringan.

**Rekomendasi:** **GO** untuk sign-off P0. Menunggu instruksi pemilik proyek sebelum melanjutkan ke tahap P1 (Work Order Engine & Workflow Tiket).
