# SSOT: ARSITEKTUR INFORMASI & NAVIGASI TERPADU (MOBILE & WEB PORTAL)
## dsBilling Enterprise & dsBilling Mobile v1.1.27 Build 10127

---

## 1. PENDAHULUAN & PRINSIP UTAMA

Dokumen ini merupakan **Single Source of Truth (SSOT)** arsitektur informasi, taksonomi menu, kamus istilah bisnis, hierarki tugas, dan pemetaan navigasi antara **dsBilling Web Portal** (`D:\dsBilling`) dan **dsBilling Mobile** (`D:\dsbilling-mobile`).

### Prinsip Harmonisasi Navigasi
1. **Satu Istilah Bisnis Baku (Unified Business Glossary):** Menghilangkan multi-penamaan untuk satu entitas bisnis yang sama (misal: "Inventory", "Stok", "Gudang Saya" distandardisasi menjadi **Stok Tangan** untuk teknisi dan **Stok Gudang** untuk logistik).
2. **Model Tugas Lapangan Multidimensi (Multi-Type Work Order Model):** 
   - **Tugas Instalasi (PSB):** Pemasangan pelanggan baru, aktivasi ONT, registrasi PPPoE/Hotspot, dan verifikasi redaman optik.
   - **Tiket Gangguan:** Penanganan keluhan, pemulihan layanan, perbaikan kabel dropcore, atau perbaikan sinyal.
   - **Pemeliharaan:** Perawatan berkala jaringan OSP, penataan jalur kabel, dan inspeksi perangkat jaringan.
   - **Ganti ONT:** Penggantian unit ONT bermasalah dengan pencatatan mutasi barcode/SN.
   - **Cabut ONT:** Penarikan perangkat pelanggan non-aktif kembali ke custody teknisi.
   - **Survey:** Pemeriksaan kelayakan coveran, jarak ODP, dan estimasi kabel bagi calon pelanggan.
   - Seluruh jenis tugas ini bernaung di bawah payung modul tunggal: **Tugas Lapangan** (`WorkOrder`).
3. **Pemisahan Peran Platform yang Jelas (Platform Role Scoping):**
   - **dsBilling Mobile** ditujukan khusus untuk **Field Force, Staff Operasional, NOC, dan Reseller**.
   - **Pelanggan Umum (Customer)** diarahkan ke **dsBilling Web Portal (`dsBilling Connect`)** via responsive SSO web browser untuk keamanan data tagihan, pembayaran payment gateway, dan privasi akun.
4. **Data Integrity & Resilient UI:** Jika terjadi kegagalan jaringan atau endpoint API tidak dapat dijangkau, UI wajib menampilkan status eksplisit "Data tidak tersedia", bukan menampilkan angka nol palsu (*false zero*).

---

## 2. KAMUS ISTILAH BISNIS RESMI (OFFICIAL BUSINESS GLOSSARY)

| Modul / Istilah Resmi | Definisi & Fungsi Bisnis | Padanan Web Portal | Padanan Android Mobile |
|---|---|---|---|
| **Ringkasan** | Dashboard utama penyaji ringkasan KPI, kuota penugasan, dan statistik operasional harian. | `Ringkasan` / `Dashboard` | `Ringkasan` / Header `MainActivity` |
| **Tugas Lapangan** | Seluruh surat perintah kerja (SPK) teknisi yang dieksekusi di lapangan. | `Tugas Lapangan` (`technician.my-jobs.index`) | `Tugas Lapangan` (`WorkOrderActivity`) |
| **Tugas Instalasi (PSB)** | SPK khusus pemasangan baru pelanggan internet (FTTH/Wireless). | `Tugas Instalasi (PSB)` (`technician.installation.wizard`) | `Instalasi (PSB)` (Tab & Quick Action) |
| **Tiket Gangguan** | Laporan gangguan atau keluhan pelanggan yang memerlukan investigasi & resolusi. | `Tiket Gangguan` (`technician.my-jobs.index?tab=tickets`) | `Tiket Gangguan` (`TicketsActivity` / Tab) |
| **Pemeliharaan** | Pekerjaan rutin berkala, maintenance kabel OSP, dan pemeliharaan perangkat jaringan. | `Pemeliharaan` (`technician.my-jobs.index?jobType=maintenance`) | `Pemeliharaan` (Filter & WorkOrder) |
| **Ganti ONT** | Penggantian perangkat ONT pelanggan lama ke serial baru dengan mutasi stok. | `Ganti ONT` (`technician.swap-ont.index`) | `Ganti ONT` (`SwapOntActivity`) |
| **Cabut ONT** | Dismantle / penarikan perangkat ONT dari pelanggan non-aktif. | `Cabut ONT` (`technician.dismantle.index`) | `Cabut ONT` (`DismantleActivity`) |
| **Survey** | Pemeriksaan kelayakan coveran titik ODP dan estimasi kabel dropcore. | `Survey` (`technician.my-jobs.index?jobType=survey`) | `Survey` (Filter & Detail Action) |
| **Pelanggan** | Master database pelanggan aktif, terisolir, dan riwayat langganan. | `Pelanggan` (`customers.index`) | `Pelanggan` (`CustomerActivity`) |
| **Perangkat** | Modul manajemen perangkat jaringan fisik & virtual (OLT, MikroTik, Switch, ONT). | `Perangkat` (`devices.index`) | `Perangkat ONT` (`DeviceActivity` / Bottom Nav) |
| **GenieACS** | Sub-modul TR-069 Auto-Configuration Server terintegrasi di dalam modul Perangkat. | `GenieACS` (`admin.genieacs.index`) | `Diagnosa ONT` (`DiagnosticActivity` / DeviceDetail) |
| **Peta Jaringan** | Visualisasi geografis ODP, ODC, rute fiber, dan lokasi pelanggan. | `Peta Jaringan` (`admin.coverage.index`) | `Peta Jaringan` (`NetworkMapActivity`) |
| **Sensus Area** | Audit lapangan untuk mencocokkan data fisik pelanggan, ODP, dan redaman. | `Sensus Area` (`technician.audit.form`) | `Sensus Area` (`CensusActivity`) |
| **Gudang & Material** | Manajemen fisik gudang penyimpanan barang dan pergerakan logistik. | `Gudang & Material` (`warehouse.index`) | `Stok & Material` (`InventoryActivity`) |
| **Katalog Material** | Master data seluruh item suku cadang, kabel, dropcore, adaptor, dan ONT. | `Katalog Material` (`admin.inventory.items`) | `Katalog Material` (`InventoryActivity` Tab 2) |
| **Stok Gudang** | Kuantitas fisik material yang tersimpan di dalam gudang cabang/pusat. | `Stok Gudang` (`warehouse.stock`) | N/A (Admin Web Only) |
| **Stok Tangan** | Material yang sedang dipegang dan menjadi tanggung jawab (*custody*) teknisi. | `Stok Tangan` (`technician.material.index`) | `Stok Tangan` (`InventoryActivity` Tab 1) |
| **Permintaan Material** | Pengajuan permohonan pengambilan material ke gudang cabang. | `Permintaan Material` (`technician.material.requisition`) | `Permintaan Material` (Action Modal) |
| **Serah Terima Material**| Alur penyerahan fisik barang dari gudang ke teknisi / antar sesama teknisi. | `Serah Terima Material` (`inventory.transfers`) | `Serah Terima Material` (Transfer Dialog) |
| **Penggunaan Material** | Pencatatan konsumsi material pada SPK tertentu. | `Penggunaan Material` (`technician.material.usage`) | `Penggunaan Material` (`WorkOrderDetailActivity`) |
| **Pengembalian Material**| Pengembalian material sisa atau bekas pakai kembali ke gudang (*return*). | `Pengembalian Material` (`technician.material.return`) | `Pengembalian Material` (Return Dialog) |
| **Absensi** | Pencatatan kehadiran harian teknisi (Clock-in, Clock-out, Geotag). | `Absensi` (`technician.attendance`) | `Absensi` (`AttendanceActivity`) |
| **Keuangan** | Invoice, transaksi kas, mutasi pembayaran, dan komisi reseller. | `Keuangan` (`billing.invoices.index`) | `Keuangan` (`ResellerCommissionActivity`) |
| **Slip Gaji** | Informasi rincian honor instalasi, gaji, dan insentif teknisi. | `Slip Gaji` (`technician.payroll.index`) | `Slip Gaji` (`PayrollActivity`) |
| **Pengaturan** | Konfigurasi sistem, tenant, hak akses, dan profil pengguna. | `Pengaturan` (`settings.index`) | `Pengaturan` (`SettingsActivity`) |

---

## 3. MATRIKS ROLE & PERMISSION (10 KONTEKS)

| No | Konteks Pengguna | Lingkup Platform | Modul yang Dapat Diakses | Batasan Otorisasi (Boundary) |
|---|---|---|---|---|
| 1 | **Android Teknisi** | Mobile App | Ringkasan, Tugas Lapangan (Instalasi, Gangguan, Pemeliharaan, Survey, Ganti/Cabut ONT), Stok Tangan, Sensus Area, Peta Jaringan, Absensi, Slip Gaji. | Dibatasi ke `branch_id` dan SPK yang di-assign (`assigned_to` / `technician_ids`). |
| 2 | **Android NOC** | Mobile App | Ringkasan, Monitoring OLT/Router, Diagnosa ONT, Peta Jaringan, SpeedTest, Audit Jaringan. | Read/Write konfigurasi teknis jaringan, no financial access. |
| 3 | **Android Reseller** | Mobile App | Ringkasan Saldo/Komisi, Daftar Pelanggan Reseller, Permintaan Registrasi Pelanggan Baru, Stok Tangan, Riwayat Pembayaran. | Dibatasi ketat ke `reseller_id` dan pelanggan binaan sendiri. |
| 4 | **Android Pelanggan** | SSO Redirect | Dialihkan secara otomatis via in-app browser ke Web Portal Pelanggan (`dsBilling Connect`). | Hanya melihat invoice sendiri, tiket sendiri, dan status koneksi sendiri. |
| 5 | **Web Administrator** | Web Portal | Seluruh Modul: Ringkasan, Pelanggan, Tugas Lapangan, Keuangan, Gudang, Perangkat, GenieACS, Peta Jaringan, Pengaturan, Tenant Management. | Akses penuh seluruh tenant atau scoped per company. |
| 6 | **Web Manager** | Web Portal | Ringkasan Operasional, Laporan Keuangan, Monitoring SPK, Otorisasi Pembelian Gudang, Manajemen SDM Teknisi. | Akses manajerial cabang (`branch_id`). |
| 7 | **Web NOC** | Web Portal | Monitoring Core Network, Integrasi OLT & GenieACS TR-069, Peta Jaringan, Manajemen IP & VLAN, Server Diagnostics. | Otorisasi level jaringan, read-only pada data keuangan. |
| 8 | **Web Teknisi** | Web Portal | Ringkasan, Tugas Lapangan, Pemasangan Baru (Wizard), Stok Tangan, Absensi, Slip Gaji, Sensus Jaringan. | Identik dengan Android Teknisi, responsif untuk workstation. |
| 9 | **Web Reseller** | Web Portal | Ringkasan Saldo, Pembelian Voucher/Topup, Manajemen Pelanggan Downline, Tiket Dukungan, Komisi. | Terisolasi per `reseller_id`. |
| 10 | **Web Pelanggan** | Web Portal | Tagihan & Pembayaran Online, Status Layanan, SpeedTest, Pembukaan Tiket Gangguan, Panduan Mandiri. | Terisolasi per `customer_id`. |

---

## 4. STANDAR STATUS SIKLUS TUGAS (STATE MACHINE)

Semua jenis tugas di Android dan Web menggunakan skema status yang terpadu:

| Status Resmi | Kode Database | Definisi & Makna Operasional | Aksi Diperbolehkan |
|---|---|---|---|
| **Menunggu** | `pending` | SPK baru diterbitkan, belum dialokasikan ke teknisi. | Penugasan (*Assign*) oleh Admin/Manager. |
| **Ditugaskan** | `assigned` | SPK telah dialokasikan ke teknisi tertentu. | Teknisi memulai pengerjaan (*Start Work*). |
| **Dalam Pengerjaan** | `in_progress` | Teknisi sedang berada di lokasi / mengeksekusi tugas. | Checklist, ukur redaman, mutasi stok, barcode scan. |
| **Menunggu Verifikasi**| `pending_approval`| Pekerjaan fisik selesai, menunggu verifikasi QC supervisor. | QC Review oleh Manager/Supervisor. |
| **Selesai** | `completed` | Tugas telah diverifikasi tuntas & QC disetujui. | Faktur terbit / tiket closed. |
| **Dibatalkan** | `cancelled` | Pekerjaan dibatalkan karena kendala coveran/pelanggan batal. | Log audit alasan pembatalan. |

---

## 5. PEMETAAN MENU TERPADU (MAPPING TABLE)

| Modul Resmi | Rute Web Portal (Laravel) | Activity / View Android | Backend Controller | Model SSOT | Endpoint Mobile API |
|---|---|---|---|---|---|
| **Ringkasan** | `/dashboard`, `/technician/dashboard` | `MainActivity.kt` | `DashboardController`, `MobileDashboardApiController` | User, WorkOrder, Ticket | `GET /api/mobile/v1/dashboard/summary` |
| **Tugas Lapangan** | `/technician/my-jobs` | `WorkOrderActivity.kt` | `MobileWorkforceApiController` | `WorkOrder` | `GET /api/mobile/v1/work-orders` |
| **Tugas Instalasi (PSB)** | `/technician/installation/wizard` | `WorkOrderDetailActivity.kt` (Type: PSB) | `MobileWorkforceApiController`, `MobileNetworkApiController` | `WorkOrder`, `Customer`, `ServiceInstance` | `POST /api/mobile/v1/network/provision-psb` |
| **Tiket Gangguan** | `/technician/my-jobs?tab=tickets` | `TicketsActivity.kt` | `MobileWorkforceApiController` | `Ticket`, `WorkOrder` | `GET /api/mobile/v1/tickets` |
| **Pemeliharaan** | `/technician/my-jobs?jobType=maintenance` | `WorkOrderActivity.kt?initial_type=MAINTENANCE` | `MobileWorkforceApiController` | `SupportMaintenance`, `WorkOrder` | `GET /api/mobile/v1/work-orders?type=maintenance` |
| **Survey** | `/technician/my-jobs?jobType=survey` | `WorkOrderActivity.kt?initial_type=SURVEY` | `MobileWorkforceApiController` | `Survey`, `WorkOrder` | `GET /api/mobile/v1/work-orders?type=survey` |
| **Ganti ONT** | `/technician/swap-ont` | `SwapOntActivity.kt` | `MobileNetworkApiController` | `OntDevice`, `StockMutation` | `POST /api/mobile/v1/network/swap-ont` |
| **Cabut ONT** | `/technician/dismantle` | `DismantleActivity.kt` | `MobileNetworkApiController` | `OntDevice`, `StockMutation` | `POST /api/mobile/v1/network/dismantle-ont` |
| **Perangkat & Diagnosa**| `/devices`, `/admin/genieacs` | `DeviceActivity.kt`, `DiagnosticActivity.kt` | `MobileAcsProxyController` | `ACSDevice`, `Onu` | `GET /api/mobile/acs/devices` |
| **Pelanggan** | `/customers` | `CustomerActivity.kt` | `MobileCustomerApiController` | `Customer` | `GET /api/mobile/v1/customers` |
| **Stok Tangan** | `/technician/material` | `InventoryActivity.kt` (Tab 1: Stok Tangan) | `MobileInventoryApiController` | `TechnicianCustody`, `StockMutation` | `GET /api/mobile/v1/inventory/my-custody` |
| **Katalog Material** | `/inventory/items` | `InventoryActivity.kt` (Tab 2: Katalog) | `MobileInventoryApiController` | `Item` | `GET /api/mobile/v1/inventory/items` |
| **Serah Terima** | `/inventory/transfers` | `InventoryActivity.kt` (Action: Transfer) | `MobileInventoryApiController` | `ItemTransfer` | `POST /api/mobile/v1/inventory/transfer` |
| **Peta Jaringan** | `/coverage/map` | `NetworkMapActivity.kt` | `MobileNetworkApiController` | `Odp`, `Odc`, `Customer` | `GET /api/mobile/v1/network/odp-nearest` |
| **Sensus Area** | `/technician/audit` | `CensusActivity.kt` | `MobileNetworkApiController` | `NetworkAudit` | `POST /api/mobile/v1/network/audit-submit` |
| **Absensi** | `/technician/attendance`| `AttendanceActivity.kt` | `MobileAttendanceApiController` | `TechnicianAttendance`| `POST /api/mobile/v1/attendance/check-in` |
| **Slip Gaji** | `/technician/payroll` | `PayrollActivity.kt` | `MobilePayrollApiController` | `TechnicianPayroll` | `GET /api/mobile/v1/payroll/my-summary` |
| **Pengaturan** | `/settings` | `SettingsActivity.kt` | `MobileProfileApiController` | `User` | `GET /api/mobile/v1/profile` |

---

## 6. FITUR KHUSUS PLATFORM (PLATFORM-SPECIFIC FEATURES)

### A. Fitur Khusus Web Portal (Tidak Ada di Android Mobile)
1. **Rekonsiliasi Keuangan & Chart of Accounts:** Jurnal akuntansi pembukuan ganda (*double-entry ledger*) memerlukan visualisasi multi-kolom untuk neraca, laba-rugi, dan batch reconciliation.
2. **Konfigurasi Master Hardware & Server Daemon:** Penambahan interface OLT ZTE/Huawei, konfigurasi server RADIUS, integrasi API payment gateway Xendit/Midtrans, dan konfigurasi TR-069 GenieACS daemons.
3. **Manajemen Gudang Pusat (Multi-Warehouse):** Manajemen stok opname massal, purchase order (PO) supplier, dan mutasi antar gudang pusat.
4. **Desain Paket Layanan & Tarif ISP:** Pembuatan profil bandwidth (CIR/MIR), IP Pool MikroTik, dan matriks harga reseller.

### B. Fitur Khusus Android Mobile (Tidak Ada di Web Portal)
1. **Offline Sync & Action Queue:** Teknisi dapat menyelesaikan SPK di area blank spot internet; mutasi dan checklist disimpan di SQLite lokal dan disinkronkan otomatis saat ada koneksi.
2. **Hardware Barcode / QR Scanner Camera:** Pemindaian instan Serial Number (SN) dan MAC Address ONU menggunakan kamera perangkat tanpa input manual.
3. **Perekaman Lokasi Presisi Real-time (GPS Geofence):** Validasi posisi teknisi saat Clock-In absensi dan validasi koordinat rumah saat menyelesaikan PSB.
4. **Tanda Tangan Digital di Layar Sentuh (Touch Signature):** Pelanggan menandatangani berita acara serah terima langsung di layar HP teknisi.
5. **Direct Integration Optical Power Meter:** Pengambilan foto hasil ukur redaman optik langsung dari kamera perangkat untuk verifikasi anti-fraud.

---

*Dokumen ini berlaku efektif untuk seluruh rilis dsBilling Mobile v1.1.27+ dan dsBilling Enterprise Web.*
