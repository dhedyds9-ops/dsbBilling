# LAPORAN VERIFIKASI INDEPENDEN — PORTAL TEKNISI DSBILLING ENTERPRISE (GATE P0)

**Tanggal Verifikasi:** 5 November 2026  
**Auditor/Verifier:** Independent Verification Gate Engine  
**Lingkungan Uji:** Test Environment dsBilling (PHP 8.2.28, SQLite/MySQL DB Driver, Local Test Suite)  
**File Acuan:** `docs/audit/TECHNICIAN_PORTAL_P0_IMPLEMENTATION.md` & Kode Aktual Sumber Proyek  

---

## 1. RINGKASAN EKSEKUTIF STATUS VERIFIKASI

| Modul Pengujian | Status Gate | Ringkasan Hasil Uji Aktual |
|---|---|---|
| **P0-1: Payroll IDOR** | **CONDITIONAL PASS** | Validasi kepemilikan teknisi (`$payroll->employee_id === $employee->id`) terbukti **PASS** (403 Forbidden pada akses orang lain & user tanpa employee). Ditemukan celah isolasi cabang: Manager dapat melihat slip gaji seluruh cabang (tidak ada branch scoping pada tabel `payrolls`). |
| **P0-2: File Upload Security** | **CONDITIONAL PASS** | Validasi tipe MIME, ekstensi gambar (JPG, PNG, WEBP), batas 5 MB, dan penolakan file PHP/SVG/EXE terbukti **PASS**. Random filename 40 karakter **PASS**. Ditemukan catatan keamanan arsitektural: disk `'public'` membuat berkas dapat diakses langsung via URL tanpa autentikasi route. |
| **P0-3: PSB Database Transaction** | **CONDITIONAL PASS** | Atomisitas transaksi `DB::transaction` dan rollback saat terjadi exception terbukti **PASS** (tidak ada data orphan). Deteksi ONU aktif pada cabang yang sama **PASS**. Ditemukan GAP: tidak ada DB unique constraint pada `onus.serial_number`, race condition kapasitas port ODP (tanpa `lockForUpdate`), dan `HasBranchScope` menyembunyikan deteksi konflik ONU antar-cabang. |
| **P0-4: Branch Isolation & Workforce** | **PASS** | Penolakan absensi teknisi tanpa cabang **PASS**. Penolakan submit PSB teknisi tanpa cabang **PASS**. Resolusi `branch_id` dari auth user (kebal manipulasi frontend) **PASS**. Pengecualian check-in untuk pengawasan Administrator/Superadmin **PASS**. |
| **Regression (NOC & Customer Portal)** | **PASS** | Seluruh 9 route inti Customer, NOC, dan Teknisi tetap utuh. Tidak ada perubahan pada view, controller, maupun layout portal Customer dan NOC. |

---

## 2. HASIL PEMERIKSAAN DETAIL PER KOMPONEN

### P0-1: PAYROLL IDOR & AUTHORIZATION

* **File Diperiksa:** [`app/Livewire/Isp/Technician/Payroll/Show.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Payroll/Show.php#L12-L38)
* **Status:** **PASS** (dengan 1 Catatan Arsitektur)

#### A. Rincian Kasus Uji & Hasil Aktual
1. **Teknisi Mengakses Slip Gajinya Sendiri:**
   - **Hasil:** `PASS (HTTP 200 OK)`.
   - **Bukti Uji:** Instance Livewire berhasil memuat model `Payroll` dan menampilkan data gaji milik teknisi yang login.
2. **Teknisi Mengakses Slip Gaji Rekan Lain (Eksploitasi ID Parameter):**
   - **Hasil:** `PASS (HTTP 403 Forbidden)`.
   - **Bukti Log:** `HttpException 403: Anda tidak memiliki hak akses untuk melihat slip gaji ini.`
3. **User Login Tanpa Record Employee:**
   - **Hasil:** `PASS (HTTP 403 Forbidden)`.
   - **Bukti Log:** `HttpException 403: Akun Anda belum terhubung dengan data pegawai (Employee). Silakan hubungi HRD atau Administrator.`
4. **Pengawasan oleh Administrator / SuperAdmin:**
   - **Hasil:** `PASS (HTTP 200 OK)`.
   - **Bukti Uji:** User dengan role `administrator` diizinkan melihat slip gaji untuk keperluan verifikasi dan penggajian.
5. **Akses Lintas Cabang oleh Manager:**
   - **Hasil:** `ALLOWED (GAP)`.
   - **Analisis:** Pada `Payroll/Show.php` baris 21-25, role `manager` dibebaskan dari pengecekan kepemilikan employee. Karena model `Payroll` maupun `Employee` tidak memiliki kolom `branch_id` dan tidak menerapkan `HasBranchScope`, seorang Manager dari Cabang A secara teknis dapat melihat slip gaji karyawan di Cabang B jika mengetahui ID payroll-nya.
* **Risiko Tersisa:** Manajer cabang dapat melihat data payroll di luar cabangnya.
* **Rekomendasi Koreksi:** Tambahkan validasi cabang pada manager:
  ```php
  if ($user->hasRole('manager') && $user->branch_id) {
      $employeeBranchId = $this->payroll->employee?->user?->branch_id;
      if ($employeeBranchId && $employeeBranchId !== $user->branch_id) {
          abort(403, 'Anda tidak berwenang melihat payroll di luar cabang Anda.');
      }
  }
  ```

---

### P0-2: FILE UPLOAD SECURITY & STORAGE

* **File Diperiksa:** 
  - [`app/Livewire/Isp/Technician/Tickets/Index.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Tickets/Index.php#L170-L195) (baris 170-195, 276-296)
  - [`app/Livewire/Isp/Technician/MyJobs/Index.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/MyJobs/Index.php#L112-L135) (baris 112-135, 172-192)
* **Status:** **PASS** (dengan 1 Temuan Risiko Privasi)

#### A. Rincian Kasus Uji & Hasil Aktual
1. **Format Valid (JPG, JPEG, WEBP < 5MB):**
   - **Hasil:** `PASS`. Berkas lolos validasi `image|mimes:jpeg,png,jpg,webp|max:5120`.
2. **File Skrip PHP Berbahaya (`backdoor.php`):**
   - **Hasil:** `PASS (Blocked)`. Validasi MIME dan format menolak file secara instan.
3. **File SVG Berisi Skrip XSS (`vector.svg`):**
   - **Hasil:** `PASS (Blocked)`. Aturan `mimes:jpeg,png,jpg,webp` menolak file SVG.
4. **File Biner Executable (`malware.exe`):**
   - **Hasil:** `PASS (Blocked)`. Ditolak oleh rule validasi.
5. **Ukuran File Melebihi Batas (> 5120 KB):**
   - **Hasil:** `PASS (Blocked)`. File 5.2 MB ditolak dengan pesan ukuran melebihi batas.
6. **Penamaan Berkas & Isolasi Path:**
   - **Hasil:** `PASS`. Menggunakan `Str::random(40) . '.' . $ext` pada folder `tickets/{ticket_id}`. Tidak ada risiko benturan nama (*file collision*) atau eksploitasi nama asli.
7. **Penyimpanan Public Storage (Privasi Berkas):**
   - **Hasil:** `PRIVACY WARNING (GAP)`.
   - **Temuan Kode:** Berkas disimpan ke disk `public` (`$photo->storeAs('tickets/' . $ticket->id, $filename, 'public')`) yang mengarah ke `storage/app/public/...` dan disimlink ke `public/storage/...`.
   - **Dampak:** Siapapun di internet yang mengetahui URL berkas (misal melalui tautan yang dibagikan atau tebakan) dapat mengunduh foto dokumentasi pekerjaan/rumah pelanggan secara langsung via HTTP GET tanpa melewati middleware autentikasi dsBilling.
* **Risiko Tersisa:** URL berkas foto lapangan terekspos secara publik tanpa sesi login.
* **Rekomendasi Koreksi (Tahap Lanjutan):** Pindahkan penyimpanan foto tiket ke disk privat (`local` disk) dan sajikan melalui Route Controller terlindungi autentikasi (`Route::get('/tickets/{ticket}/photos/{file}', [TicketPhotoController::class, 'show'])->middleware('auth')`).

---

### P0-3: TRANSAKSI DATABASE PSB & DATA INTEGRITY

* **File Diperiksa:** [`app/Livewire/Isp/Technician/Installation/Wizard.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Installation/Wizard.php#L139-L325)
* **Status:** **CONDITIONAL PASS** (Atomisitas Terverifikasi, Terdapat Gap Skema Database & Concurrency)

#### A. Rincian Kasus Uji & Hasil Aktual
1. **Atomisitas Transaksi & Pembuktian Rollback:**
   - **Hasil:** `PASS`.
   - **Bukti Uji:** Simulasi exception di dalam `DB::transaction()` membuktikan bahwa seluruh penulisan record `Customer` dan `Onu` dibatalkan seketika (`customerCountAfter === customerCountBefore` dan `onuCountAfter === onuCountBefore`). Tidak tercipta data orphan.
2. **Pencegahan Konflik ONU Aktif (Internal Cabang):**
   - **Hasil:** `PASS`.
   - **Bukti Uji:** Pengecekan pada baris 190-202 berhasil mendeteksi ONU yang sedang berstatus `active` pada `customer_services` pelanggan lain dan menghentikan proses registrasi.
3. **Database-level Constraint pada Serial Number ONU:**
   - **Hasil:** `FAIL / GAP`.
   - **Bukti Pemeriksaan:** Pemeriksaan pada migrasi tabel `onus` (`database/migrations/2026_06_27_103843_create_onus_table.php`) menunjukkan:
     ```php
     $table->string('serial_number')->nullable(); // Tidak ada ->unique()
     ```
   - **Dampak:** Jika terjadi *race condition* (dua teknisi men-submit serial number yang sama dalam milidetik yang sama), database tidak memiliki pengaman *unique index* sehingga record ONU duplikat tetap dapat terbentuk.
4. **Race Condition Kapasitas Port ODP:**
   - **Hasil:** `FAIL / GAP`.
   - **Bukti Kode:** Pada baris 181-186:
     ```php
     $odp = Odp::find($this->odp_id);
     if (!$odp || $odp->availablePortCount <= 0) { ... return; }
     ```
     Pengecekan port ODP dilakukan **sebelum** `DB::transaction()` tanpa menggunakan `lockForUpdate()`. Jika sisa port tinggal 1 dan ada 2 teknisi men-submit secara bersamaan, keduanya akan lolos validasi kapasitas (*over-subscription*).
5. **Pemisahan Aksi Eksternal Pasca-Commit:**
   - **Hasil:** `PASS`.
   - **Bukti Kode:** Pada baris 315-325, operasi eksternal (Hotspot/bridge config) dijalankan di luar blok `DB::transaction` dengan `try-catch` sehingga kegagalan jaringan eksternal tidak membatalkan registrasi database yang sah.
* **Risiko Tersisa:** Potensi race condition jika dua proses registrasi terjadi serentak pada ODP atau ONU yang sama.
* **Rekomendasi Koreksi (Untuk P1):**
  1. Tambahkan migration unique constraint pada `onus (branch_id, serial_number)`.
  2. Tambahkan `Odp::where('id', $this->odp_id)->lockForUpdate()->first()` di dalam transaksi database.

---

### P0-4: BRANCH ISOLATION & WORKFORCE GATE

* **File Diperiksa:**
  - [`app/Livewire/Isp/Technician/Attendance/Index.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Attendance/Index.php#L58-L66)
  - [`app/Livewire/Isp/Technician/Installation/Wizard.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Installation/Wizard.php#L164-L178)
  - [`app/Livewire/Isp/Technician/Audit/Form.php`](file:///d:/dsBilling/app/Livewire/Isp/Technician/Audit/Form.php#L49-L55)
  - [`app/Http/Middleware/EnsureWorkforceCheckedIn.php`](file:///d:/dsBilling/app/Http/Middleware/EnsureWorkforceCheckedIn.php#L18-L25)
* **Status:** **PASS**

#### A. Rincian Kasus Uji & Hasil Aktual
1. **Penolakan Absensi Teknisi Tanpa Penugasan Cabang:**
   - **Hasil:** `PASS`.
   - **Bukti Uji:** Teknisi dengan `branch_id = null` ditolak saat menekan Check-In:
     `"Gagal Check-In: Cabang kantor Anda belum ditentukan pada sistem. Silakan hubungi Administrator atau HRD..."`
2. **Penolakan Submit Wizard PSB Teknisi Tanpa Cabang:**
   - **Hasil:** `PASS`.
   - **Bukti Uji:** Submit PSB dihentikan dengan pesan error dan validasi form ditolak.
3. **Kebal Manipulasi Request / Parameter Frontend:**
   - **Hasil:** `PASS`.
   - **Bukti Kode:** `Wizard.php` baris 164-165 secara ketat membaca `$branchId = $user->branch_id;` dari model User yang terautentikasi di sesi backend, bukan dari input `$request` atau properti Livewire publik yang dapat diedit oleh user.
4. **Pengecualian Absensi untuk Pengawasan Administrator/SuperAdmin:**
   - **Hasil:** `PASS`.
   - **Bukti Uji:** Akun dengan role `administrator` atau `superadmin` dan `job_function !== 'TECHNICIAN'` berhasil melewati middleware `EnsureWorkforceCheckedIn` tanpa harus melakukan absensi geofence, memungkinkan pengawasan dashboard tanpa hambatan operasional.
* **Risiko Tersisa:** Reseller fallback pada baris 166-168 (`$reseller = User::find($this->new_customer_reseller_id); $branchId = $reseller?->branch_id;`) belum memvalidasi apakah reseller tersebut berada di bawah branch yang diizinkan bagi teknisi bersangkutan.

---

### REGRESSION TESTING: PORTAL CUSTOMER & NOC

* **Status:** **PASS**

1. **Integritas Route Core:**
   - `customer.dashboard` $\rightarrow$ **PASS** (Tersedia)
   - `customer.invoices` $\rightarrow$ **PASS** (Tersedia)
   - `noc.overview` $\rightarrow$ **PASS** (Tersedia)
   - `noc.dashboard` $\rightarrow$ **PASS** (Tersedia)
   - Seluruh route teknisi (`technician.dashboard`, `technician.attendance`, `technician.jobs`, `technician.installation`, `technician.tickets`) $\rightarrow$ **PASS** (Tersedia).
2. **Tampilan & Layout:**
   - Tidak ada modifikasi kode pada folder `app/Livewire/Customer`, `app/Http/Controllers/Customer`, `app/Livewire/Noc`, maupun layout `resources/views/layouts/app.blade.php`.
   - Portal Customer dan NOC 100% identik dengan kondisi sebelum pengerjaan P0.

---

## 3. RINGKASAN AKHIR AUDIT VERIFIKASI

### 1. P0 yang Benar-Benar Terverifikasi Berfungsi (VERIFIED)
* **P0-1:** Penutupan IDOR Payroll untuk teknisi biasa (403 Forbidden).
* **P0-2:** Validasi ketat tipe berkas, penolakan payload berbahaya, dan random hashing nama berkas foto tiket.
* **P0-3:** Pembungkusan mutasi database PSB ke dalam `DB::transaction` atomik dengan rollback otomatis saat kegagalan.
* **P0-4:** Penegakan batas cabang: penghapusan fallback sembarangan pada absensi dan isolasi `branch_id` pada PSB.

### 2. P0 yang Belum Dapat Dibuktikan / Terdapat GAP
* **Lock Concurrency Kapasitas ODP:** Pengecekan sisa port ODP masih rentan race condition pada pemesanan bersamaan karena dilakukan di luar transaksi tanpa `lockForUpdate()`.
* **Database Unique Constraint ONU:** Serial number ONU belum memiliki unique index di level database MySQL/SQLite.

### 3. Temuan Keamanan yang Masih Terbuka
1. **Public Storage Exposure:** Foto tiket pada `storage/app/public/tickets/` dapat dibuka langsung melalui URL browser jika tautan diketahui, tanpa melalui otorisasi role/login.
2. **Manager Cross-Branch Payroll Visibility:** Role `manager` belum dibatasi melihat payroll hanya pada cabangnya sendiri.

### 4. Risiko Regresi
* **Sangat Rendah.** Tidak ada perubahan pada skema database, tidak ada breaking changes pada API, dan isolasi kode portal teknisi terbukti tidak memengaruhi portal NOC maupun Customer.

---

## 4. REKOMENDASI: STATUS GO / NO-GO

> [!IMPORTANT]
> ### KEPUTUSAN GATE P0: **GO (DENGAN CATATAN PENYEMPURNAAN P1)**
> 
> Seluruh perbaikan fungsional kritis P0-1 s/d P0-4 telah **berhasil diterapkan dan terverifikasi secara fungsional**. Celah IDOR teknisi telah tertutup, transaksi PSB telah atomik, validasi unggah berkas telah aman dari injeksi skrip, dan penegakan cabang telah aktif.
> 
> Catatan arsitektur seperti *ODP lock concurrency*, *privatisasi disk storage foto tiket*, dan *unique index serial number* direkomendasikan untuk dimasukkan ke dalam ruang lingkup **Prioritas P1**.

---
*Laporan verifikasi independen ini selesai disusun sesuai aturan kerja READ-ONLY AUDIT. Sistem berhenti pada tahap ini untuk menunggu persetujuan dan keputusan resmi dari Pemilik Proyek.*
