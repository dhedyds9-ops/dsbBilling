# MATRIKS KESESUAIAN (PARITY MATRIX) TECHNICIAN PORTAL WEB VS DSBILLING STAFF MOBILE
## dsBilling Enterprise — Unified Field Operations Ecosystem

**Tanggal Audit:** 9 November 2026  
**Status Audit:** **SELESAI (COMPLETED)**  
**Dokumen Rujukan:**
- `app/Livewire/Isp/Technician/*` (Technician Portal Web Existing)
- `routes/web.php` (Grup `technician-portal/*` dan `inventory/*`)
- `docs/audit/TECHNICIAN_PORTAL_P0_HARDENING.md`
- `docs/audit/TECHNICIAN_PORTAL_P1_1A_PROVISIONING.md`
- `docs/audit/TECHNICIAN_PORTAL_P1_1B_DIAGNOSTICS.md`
- `docs/audit/DSBILLING_STAFF_EXISTING_APP_AUDIT.md` (Phase 1)
- `docs/audit/DSBILLING_STAFF_PHASE_3_IMPLEMENTATION.md` (Phase 2 & 3)

---

## 1. Latar Belakang & Prinsip Utama

Berdasarkan instruksi koreksi roadmap:
1. **Single Source of Truth (SSOT)**: Backend dsBilling (`D:\dsBilling`) dan Technician Portal Web existing adalah acuan utama untuk seluruh alur kerja, struktur data, business logic, peran (*role*), dan perizinan (*permissions*).
2. **Tanpa Duplikasi**: Dilarang membangun database terpisah, model terpisah, atau alur kerja operasional paralel yang menduplikasi modul dsBilling existing (Work Order, Inventory, Attendance, Payroll, Provisioning).
3. **Status Fase**: Persetujuan PHASE 2 dan PHASE 3 **tetap berlaku**. Audit ini bertujuan menyelaraskan setiap komponen mobile dengan halaman portal web yang sudah berjalan di dsBilling.

---

## 2. Pemetaan Komprehensif (Parity Matrix)

Tabel berikut memetakan setiap halaman dan fitur pada Technician Portal Web existing ke implementasi REST API backend dsBilling dan antarmuka aplikasi Android dsBilling Staff (`com.genieacs.client`).

| # | Modul / Halaman Portal Web | Route Web & Livewire Component | Model & Sumber Data Backend | Status Implementasi Mobile Saat Ini | REST API Endpoint dsBilling | Status Kesesuaian (Parity) | Tindakan Penyelarasan |
|---|---|---|---|---|---|:---:|---|
| **1** | **Dashboard Overview** | `/technician-portal/dashboard`<br>`\App\Livewire\Isp\Technician\Dashboard` | `ProvisionPipeline`, `Ticket`, `CustomerService` | Android Dashboard (`MainActivity`), KPI Performance BottomSheet | `GET /api/mobile/reports/technician-performance`<br>`GET /api/mobile/reports/activity-logs` | **SEBAGIAN (80%)** | Tambahkan metrik real-time: `countProses`, `countSelesai`, `activeTicketsCount` pada respons ringkasan mobile. |
| **2** | **Absensi & Kehadiran (Attendance)** | `/technician-portal/attendance`<br>`\App\Livewire\Isp\Technician\Attendance\Index` | `App\Models\Workforce\Attendance`, `EnsureWorkforceCheckedIn` middleware | GPS Logger (`logGPS`) | `POST /api/mobile/workforce/gps/log` | **PERLU ADAPTASI (50%)** | Tambahkan endpoint `GET /api/mobile/attendance/today`, `POST /api/mobile/attendance/check-in`, dan `POST /api/mobile/attendance/check-out` agar sinkron dengan model `Attendance`. |
| **3** | **Daftar SPK / Tugas (My Jobs)** | `/technician-portal/my-jobs`<br>`\App\Livewire\Isp\Technician\MyJobs\Index` | `WorkOrder`, `Survey`, `Installation`, `Ticket` | Android Work Orders BottomSheet (`WorkOrdersBottomSheet.kt`) | `GET /api/mobile/workforce/work-orders`<br>`GET /api/mobile/workforce/work-orders/{id}` | **PENUH (100%)** | Mendukung tab `active` dan `history`, filter tipe pekerjaan (PSB, Maintenance, Survey), dan klaim tugas. |
| **4** | **Lifecycle & Status SPK** | `/technician-portal/my-jobs/{job}`<br>`\App\Livewire\Isp\Technician\MyJobs\Show` | `WorkOrder` status transitions | Status transition dialog (`WorkOrderDetailBottomSheet.kt`) | `POST /api/mobile/workforce/work-orders/{id}/status`<br>`POST /api/mobile/workforce/work-orders/{id}/start`<br>`POST /api/mobile/workforce/work-orders/{id}/complete` | **PENUH (100%)** | Mengikuti alur resmi: `assigned` -> `in_progress` -> `pending_approval` (oleh teknisi) -> `completed` (disetujui Supervisor). |
| **5** | **Wizard Pemasangan Baru (PSB)** | `/technician-portal/installation/wizard`<br>`\App\Livewire\Isp\Technician\Installation\Wizard` | `Customer`, `ServiceProfile`, `Odp`, `Olt`, `Onu`, `ProvisioningService` | BottomSheet Scan & Provisioning ONT (`OntProvisioningBottomSheet.kt`) | `POST /api/mobile/network/devices/provision-ont` | **PENUH (100%)** | Mendukung input pelanggan, pemilihan ODP & port, input SSID/Password Wi-Fi, dan eksekusi TR-069 via backend proxy. |
| **6** | **Checklist Instalasi Lapangan** | `/technician-portal/my-jobs`<br>(Checklist SOP `odp_checked`, `cable_installed`, dll.) | `WorkOrderChecklist` | Dynamic Checklist Adapter di `WorkEvidenceBottomSheet.kt` | `POST /api/mobile/workforce/work-orders/{id}/checklists`<br>`POST /api/mobile/workforce/work-orders/{id}/checklists/{checkId}/toggle` | **PENUH (100%)** | Checklist dinamis tersimpan di tabel `work_order_checklists` dengan status centang real-time. |
| **7** | **Pengukuran Redaman Optik (OPM)** | `/technician-portal/installation/wizard` & `MyJobs/Index`<br>(Properti `$optical_power`) | `work_orders.optical_rx_power`, `optical_tx_power` | Input Form OPM di `WorkEvidenceBottomSheet.kt` | `POST /api/mobile/workforce/work-orders/{id}/measurements` | **PENUH (100%)** | Validasi batas dBm: Optimal (-14 s/d -23 dBm), Waspada (-23 s/d -26 dBm), Kritis/Loss (< -26 dBm). |
| **8** | **Dokumentasi Foto Bukti** | `/technician-portal/tickets/{id}/photos/*`<br>`TicketPhotoController` (P0-H1) | `work_orders.evidence_photos` (Private Storage Stream) | Foto Geotagged di `WorkEvidenceBottomSheet.kt` | `POST /api/mobile/workforce/work-orders/{id}/evidence` | **PENUH (100%)** | Mengunggah foto bukti (sebelum/sesudah) dengan koordinat GPS dan disimpan di private storage backend. |
| **9** | **Tanda Tangan Digital Pelanggan** | `/technician-portal/my-jobs`<br>(Properti `$digital_signature`) | `work_orders.customer_signature` | Native Canvas Pad (`SignatureView.kt`) | `POST /api/mobile/workforce/work-orders/{id}/sign` | **PENUH (100%)** | Menangkap e-signature pelanggan di layar HP dan dikirim dalam format Base64 PNG. |
| **10** | **Tiket Gangguan (Troubleshooting)** | `/technician-portal/tickets`<br>`\App\Livewire\Isp\Technician\Tickets\Index` | `App\Models\Support\Ticket` | Ditampilkan melalui Work Orders bertipe `maintenance`/`troubleshooting` | `GET /api/mobile/workforce/work-orders?type=maintenance` | **SEBAGIAN (80%)** | Tambahkan endpoint langsung `GET /api/mobile/workforce/tickets` untuk akses langsung tiket gangguan di luar SPK. |
| **11** | **Pencarian ODP & ODP Terdekat (GIS)** | `/technician-portal/odp/nearest`<br>`\App\Livewire\Isp\Technician\Odp\Nearest` | `App\Models\ISP\Odp` scope `near($lat, $lng, $radius)` | `NetworkMapActivity.kt` + ODP selector | `GET /api/mobile/network/odps?latitude={lat}&longitude={lng}` | **PENUH (100%)** | Menampilkan daftar ODP terdekat berdasarkan radius GPS teknisi, kapasitas port, dan utilisasi. |
| **12** | **Stok Teknisi (Custody)** | `/technician-portal/material`<br>(Tab `stok`) | `App\Models\Inventory\InventoryCustody` | `InventoryCustodyBottomSheet.kt` | `GET /api/mobile/inventory/my-stock` | **PENUH (100%)** | Menampilkan seluruh barang yang sedang dipegang teknisi beserta nomor seri dan jumlah. |
| **13** | **Katalog Gudang & Permintaan (Requisition)** | `/technician-portal/material`<br>(Tab `gudang`) / `inventory/requisitions` | `App\Models\Inventory\Item`, `InventoryRequisition` | `MaterialRequisitionBottomSheet` | `GET /api/mobile/inventory/items`<br>`POST /api/mobile/inventory/requisitions`<br>`GET /api/mobile/inventory/requisitions` | **PENUH (100%)** | Teknisi meminta barang dari gudang, supervisor menyetujui, dan stok otomatis masuk ke custody teknisi. |
| **14** | **Pencatatan Pemakaian Material** | `/technician-portal/material`<br>(Tab `pemakaian`) / `MyJobs` | `App\Models\Workforce\MaterialUsage`, `InventoryUsage` | `MaterialUsageBottomSheet.kt` | `POST /api/mobile/workforce/work-orders/{id}/materials` | **PENUH (100%)** | Mencatat kuantitas material terpakai (drop core, fast connector, ONT) langsung mengurangi custody. |
| **15** | **Serah Terima Material Antar Teknisi** | `/inventory/custodies`<br>(Aksi transfer custody) | `App\Models\Inventory\InventoryCustodyMutation` | Tombol Transfer di `InventoryCustodyBottomSheet.kt` | `POST /api/mobile/inventory/custody/transfer` | **PENUH (100%)** | Memindahkan tanggung jawab material dari Teknisi A ke Teknisi B secara resmi dengan audit log. |
| **16** | **Pengembalian & Retur Barang Rusak** | `/technician-portal/material`<br>(Modal Retur) | `App\Models\Inventory\InventoryReturn` | Tombol Return di `InventoryCustodyBottomSheet.kt` | `POST /api/mobile/inventory/returns` | **PENUH (100%)** | Mengembalikan sisa material atau melaporkan barang afkir/rusak kembali ke gudang pusat. |
| **17** | **Penggantian ONT (Swap ONT)** | `/technician-portal/swap-ont`<br>`\App\Livewire\Isp\Technician\Swap\Index` | `Onu`, `CustomerService`, `GenieAcsProvisioningService` | Dilakukan melalui alur re-provisioning ONT | `POST /api/mobile/network/devices/provision-ont` | **PERLU ADAPTASI (70%)** | Buat endpoint khusus `POST /api/mobile/network/devices/swap-ont` yang otomatis menyalin PPPoE dan Wi-Fi lama ke ONT baru. |
| **18** | **Diagnostik ONT & Remote Action** | `/technician-portal/diagnostic`<br>`\App\Livewire\Isp\Technician\Diagnostic\Index` | `GenieAcsDiagnosticService`, `MobileAcsProxyController` | `DeviceDiagnosticsBottomSheet.kt` | `GET /api/mobile/network/devices/{sn}/diagnostics`<br>`POST /api/mobile/acs/devices/{id}/reboot`<br>`POST /api/mobile/acs/devices/{id}/wifi`<br>`POST /api/mobile/acs/devices/{id}/summon` | **PENUH (100%)** | Membaca parameter TR-069 live (RX/TX Power, Uptime, Last Inform) serta aksi reboot, ubah Wi-Fi, dan summon. |
| **19** | **Penarikan Perangkat (Dismantle)** | `/technician-portal/dismantle`<br>`\App\Livewire\Isp\Technician\Dismantle\Index` | `Onu`, `CustomerService`, `InventoryCustody` | Ditangani melalui Work Order `dismantle` | `POST /api/mobile/workforce/work-orders/{id}/complete` | **PERLU ADAPTASI (70%)** | Sediakan endpoint khusus `POST /api/mobile/network/devices/dismantle` untuk mencatat checklist kelengkapan (modem, adaptor, patchcord, box). |
| **20** | **Slip Gaji & Payroll** | `/technician-portal/payroll`<br>`\App\Livewire\Isp\Technician\Payroll\Index` | `App\Models\Payroll`, `App\Models\Employee` | Belum ada menu khusus di mobile | *Belum tersedia* | **FITUR BARU (0%)** | Tambahkan endpoint `GET /api/mobile/payroll` dan `GET /api/mobile/payroll/{id}` untuk melihat rincian gaji teknisi di HP. |
| **21** | **Pusat Notifikasi Operasional** | Notifikasi In-App & Push Notification | `App\Models\Notification` | `NotificationsBottomSheet.kt` | `GET /api/mobile/notifications`<br>`POST /api/mobile/notifications/{id}/read` | **PENUH (100%)** | Menampilkan penugasan SPK baru, approval material, dan update status tiket. |
| **22** | **Mode Offline & Sinkronisasi** | PWA Offline Cache (Service Worker) | Cache Bundle & Batch Action Queue | `OfflineSyncManager.kt` & `OfflineSyncBottomSheet.kt` | `GET /api/mobile/sync/bootstrap`<br>`POST /api/mobile/sync/offline-batch` | **PENUH (100%)** | Menyimpan data SPK, ODP, dan stok di memori lokal saat blank-spot; otomatis sinkronisasi saat tersambung sinyal. |
| **23** | **OTA App Versioning & Release** | N/A (Web tidak memerlukan OTA) | `config/mobile.php` | `MainActivity.kt` OTA check dialog | `GET /api/mobile/app/check-update` | **PENUH (100%)** | Memeriksa ketersediaan versi baru APK dan memberikan opsi pembaruan langsung bagi teknisi. |

---

## 3. Identifikasi Gap & Kategorisasi Fitur

Dari 23 fitur operasional di atas, dilakukan pengelompokan sebagai berikut:

### Kategori A: Fitur Existing yang Sudah Selaras Sempurna (Full Parity) — 17 Fitur (74%)
Fitur-fitur ini telah terimplementasi di backend dsBilling dan aplikasi mobile Android tanpa ada perbedaan struktur data atau aturan bisnis:
1. Daftar SPK & Filter Status (`WorkOrdersBottomSheet.kt`).
2. Lifecycle Pekerjaan & Approval Supervisor (`WorkOrderDetailBottomSheet.kt`).
3. Wizard Pemasangan Baru & Scan Barcode ONT (`OntProvisioningBottomSheet.kt`).
4. Checklist Dinamis Pekerjaan Lapangan.
5. Pengukuran Redaman Optik OPM (Validasi Batas dBm).
6. Upload Foto Bukti Geotagged (Private Storage Stream).
7. Tanda Tangan Digital Pelanggan (Canvas E-Signature).
8. Pencarian ODP & Jaringan Terdekat via GPS (`NetworkMapActivity.kt`).
9. Stok Material Teknisi / Custody (`InventoryCustodyBottomSheet.kt`).
10. Katalog Barang Gudang & Pengajuan Permintaan Material (`requisitions`).
11. Pencatatan Pemakaian Material per SPK (`materials`).
12. Serah Terima Barang Antar Teknisi (`custody/transfer`).
13. Retur Material & Laporan Barang Rusak/Afkir (`returns`).
14. Diagnostik Live ONT & TR-069 Action Proxy (Reboot, Wi-Fi, Summon).
15. Pusat Notifikasi Operasional In-App (`NotificationsBottomSheet.kt`).
16. Mode Offline & Sinkronisasi Antrean Batch (`OfflineSyncManager.kt`).
17. In-App OTA Update Check & Release Keystore Signing.

---

### Kategori B: Fitur yang Perlu Diadaptasi / Diselaraskan — 5 Fitur (22%)
Fitur-fitur ini sudah memiliki fondasi di backend atau web portal, namun endpoint mobile-nya perlu diselaraskan agar 100% identik dengan alur kerja Technician Portal:
1. **Absensi & Kehadiran (Attendance)**:
   - *Kondisi Existing*: Ada endpoint GPS log, tetapi belum ada endpoint check-in/check-out harian yang terhubung ke tabel `attendances` dan middleware `EnsureWorkforceCheckedIn`.
   - *Target*: Tambahkan endpoint `GET /api/mobile/attendance/today`, `POST /api/mobile/attendance/check-in`, dan `POST /api/mobile/attendance/check-out`.
2. **Dashboard Overview Real-Time Counters**:
   - *Kondisi Existing*: Menampilkan KPI performa bulanan dan aktivitas.
   - *Target*: Tambahkan counter harian `countProses`, `countSelesai`, `activeTicketsCount` pada respons API.
3. **Penggantian ONT (Swap ONT)**:
   - *Kondisi Existing*: Teknisi dapat mem-provision ONT baru, namun belum ada endpoint *one-click swap* yang otomatis menyalin konfigurasi PPPoE dan Wi-Fi dari modem lama seperti pada `Swap\Index.php`.
   - *Target*: Tambahkan endpoint `POST /api/mobile/network/devices/swap-ont`.
4. **Dismantle / Penarikan Perangkat**:
   - *Kondisi Existing*: Ditangani sebagai penyelesaian SPK umum.
   - *Target*: Sediakan endpoint `POST /api/mobile/network/devices/dismantle` yang mencatat checklist kelengkapan barang (adaptor, patchcord, kardus) dan kondisi perangkat (baik/rusak).
5. **Akses Langsung Tiket Gangguan (Direct Tickets Access)**:
   - *Kondisi Existing*: Tiket diakses jika terkait dengan SPK.
   - *Target*: Sediakan endpoint `GET /api/mobile/workforce/tickets` untuk memudahkan teknisi melihat tiket gangguan terbuka di cabangnya.

---

### Kategori C: Fitur Baru yang Melengkapi Ekosistem — 1 Fitur (4%)
1. **Slip Gaji & Payroll Mobile**:
   - *Kondisi Existing*: Teknisi hanya bisa melihat slip gaji melalui portal web di laptop/browser.
   - *Target*: Sediakan endpoint `GET /api/mobile/payroll` dan `GET /api/mobile/payroll/{id}` agar teknisi dapat melihat slip gaji bulanan langsung dari aplikasi mobile.

---

## 4. Urutan Implementasi Berdasarkan Dependensi

Agar penyelarasan berjalan mulus tanpa merusak fitur yang sudah berfungsi dan tanpa mengulang pekerjaan yang telah disetujui:

```
[LANGKAH 1: ATTENDANCE & SHIFT INTEGRATION] (Paling Kritis)
   - Implementasikan API Absensi Hadir / Pulang (Check-in / Check-out).
   - Menjamin kepatuhan terhadap middleware `workforce.checked_in` dsBilling.
         │
         ▼
[LANGKAH 2: TICKET & SWAP/DISMANTLE ADAPTATION]
   - Endpoint Direct Tickets (`/api/mobile/workforce/tickets`).
   - Endpoint One-Click Swap ONT (`/api/mobile/network/devices/swap-ont`).
   - Endpoint Dismantle Checklist (`/api/mobile/network/devices/dismantle`).
         │
         ▼
[LANGKAH 3: PAYROLL & SELF-SERVICE STAFF]
   - Endpoint Slip Gaji Teknisi (`/api/mobile/payroll`).
   - Visualisasi rincian take-home pay, insentif PSB, dan potongan.
         │
         ▼
[LANGKAH 4: PHASE 3 VERIFIKASI AKHIR & SERTIFIKASI REGRESI]
   - Uji menyeluruh seluruh test suite PHPUnit (Absensi, SPK, Inventory, Swap, Dismantle, Payroll).
   - Verifikasi isolasi branch dan role security.
   - Rilis akhir APK bertanda tangan resmi.
```

---

## 5. Kesimpulan Audit

Hasil audit membuktikan bahwa **74% fitur operasional lapangan dsBilling telah selaras penuh antara Web Portal dan Android**. Sisa 26% merupakan penambahan endpoint penyelarasan spesifik (Absensi, Swap ONT, Dismantle, dan Payroll) yang langsung memanfaatkan Model dan Business Logic yang sudah ada di `D:\dsBilling` tanpa membuat tabel baru atau menduplikasi sistem.
