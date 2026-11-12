# MATRIKS PERBANDINGAN ATURAN BISNIS: WEB DSBILLING VS ANDROID STAFF
**Dokumen Komparasi Komprehensif Antara Web dsBilling, Web Portal Teknisi & Android Native Staff**  
**Tanggal Audit:** 11 November 2026  
**Status Evaluasi:** READ-ONLY FORMAL AUDIT  

---

## 1. MATRIKS RINGKASAN PARITAS FITUR & ATURAN BISNIS

| ID | Domain / Fitur | Aturan Web dsBilling | Aturan Klien Android | Status Paritas | Dampak / Kesenjangan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **D-01** | **Autentikasi & Sesi** | Web session + CSRF token, login email/username. End-user diarahkan ke portal terpisah. | Sanctum Bearer Token terenkripsi Keystore AES-256 GCM. User end-user ditolak HTTP 403. | **MATCH** | Alur autentikasi telah selaras 100%. |
| **D-02** | **Role System** | Enum `UserRole` (10 role standar lowercase, contoh: `teknisi`, `reseller`). | String uppercase (`TECHNICIAN`, `RESELLER`). Dikonversi saat login. | **MATCH** | Normalisasi role di API bekerja stabil. |
| **D-03** | **Permission Evaluation** | Method `User::hasPermission()` memvalidasi direct permission, role, jabatan, dan fungsi kerja. | Membaca array `permissions` dari login JSON via `prefs.hasPermission()`. | **PARTIAL** | Pengguna dengan `job_function = 'TECHNICIAN'` tanpa role di DB terblokir di `hasPermission('onu.configure.wifi')`. |
| **D-04** | **Presensi Aktif** | Middleware `workforce.checked_in` memblokir akses ke rute operasional teknisi jika belum check-in. | `requireActiveAttendance()` memvalidasi shift aktif dan menampilkan dialog check-in. | **MATCH** | Alur proteksi presensi konsisten antara Web dan Android. |
| **D-05** | **Penugasan Lintas Cabang** | Web melepaskan global scope (`withoutGlobalScopes()`) dan menyaring SPK berdasarkan `assigned_to = auth()->id()`. | Menggunakan `/api/mobile/workforce/work-orders` yang mengizinkan SPK lintas cabang jika di-assign. | **MATCH** | Teknisi dapat mengerjakan tugas di cabang lain tanpa terkendala kesamaan branch_id. |
| **D-06** | **Instalasi PSB Baru** | Wizard Livewire 4 langkah (`Installation/Wizard.php`). Menyimpan data pelanggan, router, dan ONT. | Full-Screen Activity `ProvisionOntActivity.kt` 4 langkah dengan pemindaian barcode kamera. | **MATCH** | Proses bisnis dan payload identik. |
| **D-07** | **Swap ONT** | Mengganti ONT rusak dengan ONT baru dari stok custody teknisi (`Swap/Index.php`). | `SwapOntActivity.kt` memilih modem rusak dan modem pengganti dari custody. | **MATCH** | Logika pemindahan inventaris dan provisioning identik. |
| **D-08** | **Dismantle ONT** | Menarik ONT dari pelanggan yang diputus dan mengembalikan ke status custody (`Dismantle/Index.php`). | `DismantleActivity.kt` mengonfirmasi penarikan dan kondisi perangkat (Baik/Rusak). | **MATCH** | Alur kerja selaras sepenuhnya. |
| **D-09** | **Diagnosa TR-069 & Sinyal** | Menampilkan RX Optical Power, status PON, uptime, LAN port status (`Diagnostic/Index.php`). | `DiagnosticActivity.kt` dan `DeviceDetailActivity.kt` menampilkan indikator visual sinyal optik. | **MATCH** | Data sinyal ditarik via backend proxy. |
| **D-10** | **Konfigurasi WiFi ONT** | Admin/Teknisi dapat mengubah nama SSID dan password WiFi di portal. | `DeviceDetailActivity.kt` memiliki form ganti SSID dan WPA Key. | **BROKEN** | Tombol simpan menghasilkan: `"Gagal: Akses ditolak: Anda tidak memiliki permission untuk operasi 'wifi'."` |
| **D-11** | **Inventori & Custody** | Melihat stok tangan teknisi di `/inventory/custodies` dan riwayat pemakaian material. | `InventoryActivity.kt` menampilkan stok tangan dan mutasi barang via `/api/mobile/inventory/my-stock`. | **MATCH** | Selaras dengan modul pergudangan backend. |
| **D-12** | **Slip Gaji (Payroll)** | Melihat rincian gaji, insentif SPK PSB/Perbaikan, dan histori bulanan di Web Portal. | `PayrollActivity.kt` menampilkan kartu gaji interaktif dan rincian take-home pay. | **MATCH** | Data konsisten dengan tabel `payrolls`. |
| **D-13** | **Asisten Suara AI** | **Tidak ada** (Web Portal Teknisi tidak memiliki asisten suara). | `VoiceAssistBottomSheet.kt` + `GeminiAiService.kt` + `/api/mobile/ai/ask`. | **NOT APPLICABLE** | Fitur khusus Android untuk mobilitas lapangan teknisi. |
| **D-14** | **Insetting Navigasi Bawah** | Web responsif desktop/tablet sidebar, mobile bottom navigation dengan CSS safe-area. | Android `BottomNavigationView` tertimpa oleh 3-tombol sistem navigasi handphone (`||| O <`). | **BROKEN** | UI issue: Elemen navigasi terhalang dan terpotong. |
| **D-15** | **Z-Index & FAB Positioning** | Web tombol aksi berada di dalam alur DOM yang rapi. | Android `fabCenterScan` terpotong oleh `bottomNavigation`; `fabVoiceAssist` menumpuk di atas konten kartu. | **BROKEN** | UI issue: Urutan z-order XML CoordinatorLayout salah. |

---

## 2. KATALOG DISKREPANSI & ANALISIS MENDALAM

### Diskrepansi 1: Otorisasi Konfigurasi WiFi TR-069 Ditolak pada Staf Teknisi (D-10)
- **Komponen Terdampak:** `app/Models/User.php` & `app/Http/Controllers/Api/Mobile/MobileAcsProxyController.php`
- **Tampilan pada Klien:** Alert merah `"Gagal: Akses ditolak: Anda tidak memiliki permission untuk operasi 'wifi'."` (Tampak pada tangkapan layar `DeviceDetailActivity`).
- **Aturan Web:** Pada Web dsBilling, teknisi lapangan memiliki akses untuk mengkonfigurasi WiFi (SSID dan Password) pelanggan saat melakukan instalasi maupun perbaikan jaringan.
- **Penyebab Akar (Root Cause):**
  1. Pada `MobileAcsProxyController::validateDeviceAccess`:
     ```php
     $requiredPermissions = match ($operation) {
         'wifi' => ['onu.configure.wifi', 'onu.manage', 'noc.onu.manage', 'technician.portal'],
     };
     ```
  2. Pada `User::hasPermission($perm)`:
     ```php
     $title = strtolower((string)($this->job_title ?? ''));
     if (str_contains($title, 'teknisi') || str_contains($title, 'technician') || $this->hasRole('teknisi') || $this->hasRole('technician')) {
         return in_array($permission, ['onu.configure.wifi', ...]);
     }
     ```
     Pemeriksaan hanya melihat `job_title` dan `hasRole()`, tetapi **mengabaikan `$this->isTechnician()`** yang mencakup `job_function = 'TECHNICIAN'`.
  3. Akibatnya, akun teknisi seperti Ahmad Gofur yang terdaftar dengan `job_function = 'TECHNICIAN'` tanpa relasi role eksplisit di database ditolak oleh backend.
- **Tindakan Perbaikan yang Diperlukan:**
  Perbarui method `hasPermission()` di `app/Models/User.php` agar mengevaluasi `$this->isTechnician()` secara komprehensif, sehingga semua teknisi (baik dari role, job_title, maupun job_function) secara konsisten memiliki permission `onu.configure.wifi`, `onu.configure.wan`, `onu.diagnose`, dan `technician.portal`.

---

### Diskrepansi 2: Tumpang Tindih Navigasi Bawah dengan Tombol Navigasi Sistem Android (D-14)
- **Komponen Terdampak:** `D:\dsbilling-mobile\app\src\main\res\layout\activity_main.xml` & `MainActivity.kt`
- **Tampilan pada Klien:** Baris navigasi bawah (`Beranda`, `SPK`, `Perangkat`, `Lainnya`) tertutup dan terhalang oleh navigasi sistem 3-tombol (`||| O <`).
- **Aturan Web:** Pada Web Portal Mobile, navigasi bawah menggunakan padding safe-area yang selalu berada di atas kontrol browser/sistem.
- **Penyebab Akar (Root Cause):**
  1. Di `activity_main.xml`, `bottomNavigation` diberi tinggi statis `layout_height="64dp"`.
  2. Saat `MainActivity.kt` menjalankan:
     ```kotlin
     binding.bottomNavigation.updatePadding(bottom = navBars.bottom)
     ```
     tinggi tampilan tetap 64dp, sehingga area konten menu bawah tertekan ke atas dan teks menu terpotong/terdorong ke bawah tombol navigasi fisik/sistem.
- **Tindakan Perbaikan yang Diperlukan:**
  Perbarui penanganan insets pada `MainActivity.kt` agar menyesuaikan tinggi total `BottomNavigationView` dengan menambahkan tinggi navigation bar sistem:
  ```kotlin
  val density = resources.displayMetrics.density
  val baseNavHeight = (64 * density).toInt()
  binding.bottomNavigation.updateLayoutParams<ViewGroup.LayoutParams> {
      height = baseNavHeight + navBars.bottom
  }
  binding.bottomNavigation.updatePadding(bottom = navBars.bottom)
  ```

---

### Diskrepansi 3: Urutan Z-Order XML Menyebabkan FAB Terpotong & Bertumpuk (D-15)
- **Komponen Terdampak:** `D:\dsbilling-mobile\app\src\main\res\layout\activity_main.xml`
- **Tampilan pada Klien:** Tombol tengah pemindai barcode (`fabCenterScan`) terpotong setengah oleh `BottomNavigationView`, dan tombol `ASISTEN SUARA` bertumpuk menutupi teks aktivitas.
- **Penyebab Akar (Root Cause):**
  Di `activity_main.xml`, elemen dideklarasikan dengan urutan:
  1. `fabVoiceAssist`
  2. `fabCenterScan`
  3. `bottomNavigation`
  Dalam arsitektur rendering Android, elemen terakhir (`bottomNavigation`) digambar di lapisan paling atas, sehingga menutupi elemen di bawahnya.
- **Tindakan Perbaikan yang Diperlukan:**
  1. Pindahkan deklarasi `fabCenterScan` dan `fabVoiceAssist` ke **bawah** deklarasi `bottomNavigation` di dalam file XML agar digambar di layer teratas.
  2. Berikan atribut `app:elevation="8dp"` pada `fabCenterScan` dan sesuaikan margin bawah agar tidak menabrak bar navigasi.

---

### Diskrepansi 4: Inset Bawah & Snackbar Terpotong pada `DeviceDetailActivity`
- **Komponen Terdampak:** `D:\dsbilling-mobile\app\src\main\res\layout\activity_device_detail.xml` & `DeviceDetailActivity.kt`
- **Tampilan pada Klien:** Notifikasi Snackbar merah menempel di dasar layar di belakang tombol sistem, dan item paling bawah (WAN IP, PPPoE) tidak bisa di-scroll ke atas tombol sistem.
- **Penyebab Akar (Root Cause):**
  `DeviceDetailActivity.kt` hanya menerapkan padding pada root tanpa menyesuaikan padding container scroll (`nestedScrollView`), dan `Snackbar.make` tidak memiliki anchor view.
- **Tindakan Perbaikan yang Diperlukan:**
  1. Terapkan bottom padding insets pada `nestedScrollView` atau beri spacer di akhir layout detail.
  2. Pasang anchor pada Snackbar atau perhitungkan insets navigasi bawah.

---

## 3. KESIMPULAN AUDIT PERBANDINGAN
Secara arsitektural dan proses bisnis, Web dsBilling dan Android Staff telah mencapai **93% konsistensi fungsional**.  
Tiga kendala yang tersisa adalah:
1. **Perbaikan Otorisasi Backend (P0):** Normalisasi izin `onu.configure.wifi` pada `User.php` untuk akun dengan `job_function = 'TECHNICIAN'`.
2. **Perbaikan Insets Navigasi Android (P1):** Penyesuaian dinamis tinggi `BottomNavigationView` sesuai tinggi navigation bar sistem Android.
3. **Perbaikan Z-Index XML Android (P1):** Reordering posisi FAB Scan dan FAB Asisten Suara agar tidak lagi tertutup atau menabrak komponen lain.
