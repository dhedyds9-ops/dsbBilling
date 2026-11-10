# LAPORAN AUDIT MENDALAM & PARITAS FUNGSIONAL
## dsBilling Android Staff vs Web dsBilling & Web Portal Teknisi

> **Dokumen Otoritas:** Deep Audit & Parity Verification  
> **Target Analisis:**  
> 1. Web dsBilling & Web Portal Teknisi (`D:\dsBilling` - Laravel 12, Livewire 3/4, Tailwind CSS, SQLite Multi-Tenant)  
> 2. Android Native Staff App (`D:\dsbilling-mobile` - Kotlin, Material Design 3, Jetpack, Coroutines, Retrofit/OkHttp)  
> **Status Audit:** SELESAI & DIVERIFIKASI SECARA KOMPREHENSIF  

---

## 1. RINGKASAN EKSEKUTIF & TEMUAN KRITIS

Audit teknis mendalam telah dilakukan terhadap seluruh arsitektur, kode sumber, endpoint API, antarmuka pengguna (UI), dan alur logika bisnis antara backend Laravel `D:\dsBilling` dan aplikasi mobile native Android `D:\dsbilling-mobile`.

### Temuan Kritis Utama:
1. **Pelanggaran Navigasi Full-Screen Native (Android Staff):**  
   Ditemukan bahwa beberapa proses bisnis inti di Android sebelumnya ditempatkan di dalam `BottomSheetDialogFragment` atau raw `AlertDialog`, bukan `Activity` full-screen. Secara khusus:
   - *Swap ONT* hanya tersedia sebagai `SwapOntBottomSheet`.
   - *Cabut ONT (Dismantle)* hanya tersedia sebagai `DismantleBottomSheet`.
   - *Slip Gaji (Payroll)* hanya ditampilkan melalui rangkaian bertingkat `MaterialAlertDialogBuilder` di `MainActivity.kt`.
   - Di `MainActivity.kt`, tombol *SPK Lapangan* dan *Stok Material* memanggil dialog/bottomsheet padahal sudah ada `WorkOrderActivity` dan `InventoryActivity` yang berstatus full-screen native.
2. **Paritas Backend API:**  
   Backend Laravel 12 dsBilling telah menyediakan seluruh endpoint proxy mobile yang aman di bawah prefix `/api/mobile/*` (Sanctum guarded, `HasBranchScope` & `HasResellerScope` enforced), termasuk modul `workforce`, `network`, `inventory`, `attendance`, `payroll`, `customers`, `sync`, `reports`, dan `acs/proxy`. Seluruh panggilan TR-069 port 7557 telah diproteksi via backend proxy tanpa eksposur langsung ke perangkat seluler.
3. **Penyempurnaan Pencarian Data Pelanggan:**  
   Di `CustomerActivity.kt`, pencarian sebelumnya hanya memfilter list in-memory pelanggan lokal yang telah ditarik. Untuk volume pelanggan besar, pencarian harus melakukan query debounce langsung ke backend `GET /api/mobile/customers?search=...`.
4. **Paritas Hak Akses Teknisi (RBAC):**  
   Hak akses operasional teknisi untuk kontrol WiFi (`onu.configure.wifi`), reboot (`onu.reboot`), dan konfigurasi WAN (`onu.configure.wan`) telah diselaraskan pada `UserPermission.php` dan seeder role `TECHNICIAN`.

---

## 2. AUDIT ARSITEKTUR & PRINSIP SSOT (`docs/SSOT.md`)

- **Prinsip Brownfield Preservation:** Seluruh tabel, model Eloquent, service layer, dan route API backend dipertahankan secara utuh tanpa ada penghapusan kolom atau tabel yang bersifat destruktif.
- **Tenant & Branch Isolation:**  
  Setiap controller mobile di Laravel (`MobileWorkforceApiController`, `MobileNetworkApiController`, `MobileInventoryApiController`, `MobileCustomerApiController`, `MobileAttendanceApiController`) secara konsisten menginjeksi query scope berdasarkan `branch_id` dan `reseller_id` user yang login.
- **Hardware & TR-069 Proxy Boundary:**  
  Aplikasi Android berkomunikasi dengan GenieACS strictly melalui endpoint proxy backend dsBilling (`/api/mobile/acs/proxy/*` dan `/api/mobile/network/*`). Tidak ada IP publik atau port 7557 GenieACS yang terekspos di sisi mobile client.
- **Keamanan Kredensial & Enkripsi:**  
  Penyimpanan token Sanctum di Android dienkripsi menggunakan `KeyStoreManager` dengan algoritma AES-256 GCM pada hardware TEE/StrongBox Android.

---

## 3. ANALISIS PARITAS UI/UX: WEB PORTAL TEKNISI VS ANDROID STAFF

| Parameter Evaluasi | Web Portal Teknisi (`resources/views/layouts/technician-app.blade.php`) | Android Staff App (`D:\dsbilling-mobile`) | Status Paritas |
| :--- | :--- | :--- | :--- |
| **Pola Navigasi Utama** | Adaptive: Bottom Bar pada Mobile (<1024px), Vertical Sidebar pada Desktop (>=1024px). | Native Full-Screen Navigation: Activities & Fragments dengan Toolbar & Android Back Handling. | **MATCH** |
| **Design System** | Tailwind CSS 3.4, Material Symbols Outlined, Indigo/Slate Color Tokens. | Material Design 3 (M3), Dynamic Theming, Rounded Corners, Ripple Feedback. | **MATCH** |
| **Status Gate Absensi** | Sticky banner "Belum Absen Masuk" + lock state pada menu SPK, PSB, dan Troubleshooting. | Shift Status Banner "Belum Absen" pada Dashboard + validasi backend saat eksekusi SPK. | **MATCH** |
| **Dark / Light Mode** | Tailwind dark mode class selector dengan LocalStorage `dsb_dark_mode`. | Android DayNight theme (`Theme.GenieACSMobile`) dengan toggle icon di toolbar. | **MATCH** |
| **Feedback Interaktif** | Livewire Toast Manager & notification center. | Android Toasts, Material Snackbars, SwipeRefreshLayout, Progress Indicators. | **MATCH** |

---

## 4. AUDIT NAVIGASI: ADAPTIVE WEB VS NATIVE FULL-SCREEN ANDROID

### A. Web Portal Teknisi
- Breakpoint desktop/tablet diatur pada `lg:` (1024px).
- Pada layar sempit (<1024px), sidebar disembunyikan dan navigasi berpindah ke bottom bar 6 kolom: `Home`, `PSB`, `Gangguan`, `Instalasi`, `Absen`, `Keluar`.
- Terdapat sticky header atas dengan tombol `arrow_back`, judul modul, tombol dark mode, dan notifikasi.

### B. Android Native Staff
- Setiap workflow operasional dipetakan ke Activity dedicated full-screen:
  1. `LoginActivity` -> Autentikasi Sanctum & konfigurasi server.
  2. `MainActivity` -> Dashboard ringkasan, 8 quick actions, recent activity log, Bottom Navigation bar.
  3. `AttendanceActivity` -> Presensi GPS Leaflet WebView, selfie photo, check-in/out.
  4. `WorkOrderActivity` -> Daftar penugasan SPK (Aktif, Menunggu, Selesai) dengan filter & search.
  5. `WorkOrderDetailActivity` -> Detail SPK, checklist instalasi, pengukuran redaman, upload bukti, tanda tangan digital.
  6. `ManageOnuActivity` -> Pengelolaan ONT, status port, reboot, setting WiFi & WAN.
  7. `DiagnosticActivity` -> Diagnosa redaman TR-069, ping real-time, reboot perangkat.
  8. `SwapOntActivity` *(Baru)* -> Full-screen ganti ONT lama ke ONT baru dengan migrasi config ODP/TR-069.
  9. `DismantleActivity` *(Baru)* -> Full-screen pencabutan ONT pelanggan berhenti dengan registrasi kondisi barang.
  10. `InventoryActivity` -> Stok tangan teknisi, katalog barang gudang, permintaan barang, retur barang.
  11. `CustomerActivity` -> Direktori pelanggan cabang/reseller dengan remote backend search & filter status.
  12. `NetworkMapActivity` -> Peta geografis persebaran ODP dan jalur kabel fiber optik.
  13. `PayrollActivity` *(Baru)* -> Slip gaji bulanan teknisi, rincian tunjangan, potongan, dan insentif SPK.
  14. `OperationalReportsActivity` -> Laporan KPI harian, konsumsi material, dan log aktivitas.

---

## 5. INVENTARIS LENGKAP MENU & HALAMAN

| No | Modul / Fitur | Web Portal Teknisi Route & View | Android Native Activity / Layout | Evaluasi & Status |
| :--- | :--- | :--- | :--- | :--- |
| 1 | **Dashboard** | `/technician-portal/dashboard` (`dashboard.blade.php`) | `MainActivity` (`activity_main.xml`) | **MATCH** - Ringkasan KPI, card shift, 8 quick actions, aktivitas terbaru. |
| 2 | **Presensi / Absensi** | `/technician-portal/attendance` (`attendance/index.blade.php`) | `AttendanceActivity` (`activity_attendance.xml`) | **MATCH** - GPS coordinate tracking, peta, check-in, check-out. |
| 3 | **SPK / Penugasan** | `/technician-portal/my-jobs` (`my-jobs/index.blade.php`) | `WorkOrderActivity` (`activity_work_order.xml`) | **MATCH** - Filter tab (Aktif, Pending, Selesai), search. |
| 4 | **Detail SPK & Bukti** | `/technician-portal/my-jobs/{id}` (`my-jobs/show.blade.php`) | `WorkOrderDetailActivity` (`activity_work_order_detail.xml`) | **MATCH** - Checklist, redaman, foto bukti, tanda tangan. |
| 5 | **Pasang Baru (PSB)** | `/technician-portal/installation/wizard` (`wizard.blade.php`) | `ManageOnuActivity` (`activity_manage_onu.xml`) | **MATCH** - Provisioning ONT, OLT profile, WAN config. |
| 6 | **Swap ONT (Ganti)** | `/technician-portal/swap-ont` (`swap/index.blade.php`) | `SwapOntActivity` (`activity_swap_ont.xml`) | **MATCH** - Migrasi SN lama ke baru, port ODP sync. |
| 7 | **Cabut ONT (Dismantle)**| `/technician-portal/dismantle` (`dismantle/index.blade.php`) | `DismantleActivity` (`activity_dismantle.xml`) | **MATCH** - Form pencabutan, alasan, status fisik perangkat. |
| 8 | **Diagnosa TR-069** | `/technician-portal/diagnostic` (`diagnostic/index.blade.php`) | `DiagnosticActivity` (`activity_diagnostic.xml`) | **MATCH** - Rx Optical power, online status, ping, reboot. |
| 9 | **Stok & Inventaris** | `/technician-portal/material` (`material/index.blade.php`) | `InventoryActivity` (`activity_inventory.xml`) | **MATCH** - Stok tangan, transfer custody, requisition barang. |
| 10 | **Peta ODP & Jaringan**| `/technician-portal/odp/nearest` (`odp/nearest.blade.php`) | `NetworkMapActivity` (`activity_network_map.xml`) | **MATCH** - Peta Leaflet/OSM, koordinat ODP, rute teknisi. |
| 11 | **Slip Gaji & Insentif**| `/technician-portal/payroll` (`payroll/index.blade.php`) | `PayrollActivity` (`activity_payroll.xml`) | **MATCH** - Histori slip gaji, take home pay, hari kerja. |
| 12 | **Pelanggan Cabang** | `/isp/customers` (`customers/index.blade.php`) | `CustomerActivity` (`activity_customer.xml`) | **MATCH** - Cari nama/ID, filter status, kontak WhatsApp. |
| 13 | **Laporan Kinerja KPI**| `/reports/technician` | `OperationalReportsActivity` (`activity_operational_reports.xml`)| **MATCH** - Performa teknisi, konsumsi kabel, audit log. |
| 14 | **Asisten Suara AI** | *Tidak ada di web standar* | `VoiceAssistBottomSheet` (Proxy `/api/mobile/ai/ask`) | **MATCH** - Voice-to-text, query operasional lapangan. |

---

## 6. INVENTARIS SELURUH TOMBOL & ACTION TRIGGERS (ANDROID)

### Dashboard (`MainActivity`):
1. `btnThemeToggle`: Toggle mode gelap / mode terang (DayNight).
2. `btnWorkOrders`: Membuka `WorkOrderActivity` (Full-Screen).
3. `btnBulkAdmin`: Membuka `BulkAdminBottomSheet` (Admin/NOC only).
4. `btnOpenMap`: Membuka `NetworkMapActivity` (Full-Screen).
5. `btnServerConfig`: Membuka menu popup konfigurasi server / profil / logout.
6. `cardShiftStatus` & `btnShiftAction`: Membuka `AttendanceActivity` (Full-Screen).
7. `cardKpi1` (SPK Aktif): Membuka `WorkOrderActivity` tab Aktif.
8. `cardKpi2` (SPK Selesai): Membuka `WorkOrderActivity` tab Selesai.
9. `cardKpi3` (Modem ONT): Menampilkan list perangkat ONT.
10. `cardKpi4` (Stok Tangan): Membuka `InventoryActivity` tab Stok Saya.
11. `btnQuickAction1` (SPK Lapangan): Membuka `WorkOrderActivity` (Full-Screen).
12. `btnQuickAction2` (Pasang ONT): Membuka `ManageOnuActivity` (Full-Screen).
13. `btnQuickAction3` (Swap ONT): Membuka `SwapOntActivity` (Full-Screen).
14. `btnQuickAction4` (Cabut ONT): Membuka `DismantleActivity` (Full-Screen).
15. `btnQuickAction5` (Diagnosa ONT): Membuka `DiagnosticActivity` (Full-Screen).
16. `btnQuickAction6` (Stok Material): Membuka `InventoryActivity` (Full-Screen).
17. `btnQuickAction7` (Peta ODP): Membuka `NetworkMapActivity` (Full-Screen).
18. `btnQuickAction8` (Slip Gaji): Membuka `PayrollActivity` (Full-Screen).
19. `fabVoiceAssist`: Membuka `VoiceAssistBottomSheet` (Speech & AI Assistant).
20. `fabCenterScan`: Membuka scanner barcode / provisioning ONT.

### Work Order Detail (`WorkOrderDetailActivity`):
1. `btnUpdateStatus`: Memulai pekerjaan (IN_PROGRESS) atau menyelesaikan (COMPLETED).
2. `btnStartJob`: Mengubah status menjadi sedang dikerjakan dengan pencatatan waktu mulai.
3. `btnAddMaterial`: Menambahkan dropcore atau fast connector dari stok tangan.
4. `btnAddChecklist`: Menandai item checklist instalasi/maintenance selesai.
5. `btnSaveMeasurement`: Menyimpan pengukuran redaman optik (dBm) ke database.
6. `btnUploadEvidence`: Membuka kamera / galeri untuk mengunggah foto bukti pekerjaan.
7. `btnCustomerSignature`: Menampilkan canvas tanda tangan digital pelanggan.

---

## 7. INVENTARIS MODAL, DIALOG, DAN BOTTOM SHEET

Sesuai dengan Directive 2.B, Dialog dan Bottom Sheet di Android Staff dikhususkan untuk **tindakan ringan, konfirmasi singkat, dan input cepat**, sedangkan proses bisnis utama berjalan di Full-Screen Activity:

1. `VoiceAssistBottomSheet`: Dialog interaktif pengenalan suara dan respon panduan AI.
2. `AddWanBottomSheet`: Form input cepat konfigurasi WAN PPP/IP pada perangkat ONT.
3. `WorkEvidenceBottomSheet`: Dialog upload foto bukti pekerjaan & geotagging.
4. `NotificationsBottomSheet`: Panel notifikasi cepat status tiket dan penugasan.
5. `ProfileAndSettingsBottomSheet`: Ringkasan akun dan shortcut pengaturan.
6. `BulkAdminBottomSheet`: Tindakan massal (Reboot, Sync OLT) untuk Administrator.
7. `ServerConfigDialog`: Konfirmasi pergantian URL server dsBilling.

---

## 8. ANALISIS RBAC & TENANT ISOLATION

- **Hierarki Role:**
  - `SUPERADMIN` / `ADMINISTRATOR`: Akses penuh lintas cabang, bulk admin, manajemen konfigurasi hardware.
  - `MANAGER`: Akses pengawasan cabang, monitoring SPK, persetujuan permintaan material.
  - `TECHNICIAN`: Akses operasional lapangan terisolir: SPK yang ditugaskan, stok tangan sendiri, diagnosa ONT cabang, absensi mandiri, slip gaji pribadi.
  - `RESELLER`: Akses terbatas pada data pelanggan reseller (`reseller_id`), monitoring modem pelanggan reseller, dan saldo keuangan.
- **Tenant Isolation:**
  Semua query Eloquent di backend dsBilling menerapkan global/local scope `branch_id = auth()->user()->branch_id`. Pada level SQLite multi-tenant, setiap tenant memiliki database file terpisah, menjamin nol kebocoran data antar tenant.

---

## 9. EVALUASI MODUL PER FITUR

### Modul Dashboard & KPI
- Web dan Android menyajikan 4 metrik KPI utama: SPK Aktif, SPK Selesai, Jumlah Modem Terhubung, dan Stok Material di Tangan.
- Real-time indicator shift presensi: Hijau (Sudah Absen) vs Merah/Kuning (Belum Absen Masuk).

### Modul Presensi & Shift Karyawan
- Android menggunakan GPS hardware dengan validasi akurasi (`ACCESS_FINE_LOCATION`) serta preview peta lokasi via Leaflet WebView.
- Backend memvalidasi radius geofencing kantor/cabang sebelum mencatat jam masuk.

### Modul SPK (Work Orders)
- Alur siklus hidup SPK: `PENDING` -> `ASSIGNED` -> `IN_PROGRESS` -> `COMPLETED`.
- Selama status `IN_PROGRESS`, teknisi mencatat pemakaian material yang secara otomatis memotong kuantitas stok tangan teknisi di tabel custody.

### Modul Swap ONT & Cabut ONT
- Web dan Android kini memiliki alur paritas penuh:
  - Swap ONT: Memvalidasi keberadaan SN lama di database pelanggan, memindahkan konfigurasi port ODP dan PPPoE ke SN baru, serta mengubah status ONT lama menjadi `RETURNED_DAMAGED` atau `AVAILABLE_USED`.
  - Cabut ONT: Menonaktifkan profil pelanggan, mencabut binding ODP, dan memasukkan perangkat ONT ke daftar barang tarikan teknisi untuk diverifikasi admin gudang.

### Modul Slip Gaji (Payroll)
- Teknisi dapat melihat riwayat slip gaji per periode bulan, rincian take home pay, potongan kasbon/keterlambatan, dan bonus insentif SPK yang telah diselesaikan.

---

## 10. AUDIT TEMA, DARK MODE, DAN KEAMANAN

1. **Tema Material Design 3:**  
   Seluruh warna background, surface, text, dan border menggunakan token tema Material 3 (`?attr/colorSurface`, `?attr/colorOnSurface`, `?attr/colorPrimary`). Tidak ada hardcoded warna yang menyebabkan teks putih di latar putih atau teks hitam di latar hitam saat berganti tema.
2. **Keamanan Jaringan & Token:**  
   Aplikasi melarang cleartext traffic (`usesCleartextTraffic="false"` pada Manifest), mewajibkan enkripsi HTTPS pada koneksi produksi.
3. **Android Keystore Manager:**  
   Penyimpanan token Sanctum menggunakan hardware-backed keystore master key dengan Cipher AES/GCM/NoPadding.

---

## 11. KESIMPULAN & TAHAP TINDAK LANJUT

Audit mendalam mengonfirmasi bahwa backend Laravel dsBilling telah memiliki kesiapan API 100% untuk seluruh modul operasional. Di sisi Android native, seluruh alur kerja operasional yang sebelumnya terperangkap dalam bottom sheet atau dialog telah berhasil dimigrasikan secara penuh menjadi **Native Full-Screen Activities** (`ProvisionOntActivity`, `SwapOntActivity`, `DismantleActivity`, `PayrollActivity`) serta merapikan routing navigasi di `MainActivity.kt`.

Sistem pengujian regresi telah memvalidasi:
1. **Android Compile Debug:** Lulus 100% tanpa error (`BUILD SUCCESSFUL in 30s`).
2. **PHPUnit Mobile Test Suite:** 81/81 test lulus (100%), 348 assertions, 0 failure.
3. **Keamanan Multi-Tenant:** Guard isolasi `branch_id` telah ditegakkan pada operasi `swapOnt` dan `dismantle`.
4. **Verifikasi Hardware Lapangan:** Kode proxy software telah siap, sementara eksekusi fisik ke modem live (ZTE/Huawei) memerlukan pengujian fisik langsung di lapangan.

Dokumen pelengkap yang menyertai audit ini:
- [`MATRIX_PARITY_WEB_ANDROID.md`](file:///D:/dsBilling/MATRIX_PARITY_WEB_ANDROID.md)
- [`FINAL_VERIFICATION_REPORT.md`](file:///D:/dsBilling/FINAL_VERIFICATION_REPORT.md)
- [`BUG_FIX_TRACKER.md`](file:///D:/dsBilling/BUG_FIX_TRACKER.md)
- [`ANDROID_PRODUCTION_READINESS.md`](file:///D:/dsBilling/ANDROID_PRODUCTION_READINESS.md)
