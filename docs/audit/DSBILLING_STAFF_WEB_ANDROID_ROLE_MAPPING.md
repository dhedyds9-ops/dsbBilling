# PEMETAAN ROLE & NAVIGASI: DSBILLING WEB vs ANDROID MOBILE
## Single Source of Truth (SSOT) Alignment Matrix

**Tanggal Dokumen:** 09 November 2026  
**Status Evaluasi:** AUDIT PEMETAAN AWAL (PHASE 2)  
**Dokumen Backend Acuan:** `D:\dsBilling` (`app/Navigation/MenuRegistry.php`, `routes/web.php`, `app/Enums/UserRole.php`, `app/Enums/UserPermission.php`)  
**Dokumen Mobile Evaluasi:** `D:\dsbilling-mobile` (`MainActivity.kt`, `activity_main.xml`, `DsBillingApiService.kt`)  

---

## 1. Prinsip Utama Keselarasan Role (SSOT Principles)

1. **Role ≠ Permission:** Role menentukan portal default yang diakses pengguna. Permission menentukan tindakan atau tombol spesifik yang boleh dijalankan di dalam portal tersebut.
2. **Backend adalah Single Source of Truth (SSOT):** Android adalah antarmuka mobile dari web dsBilling. Struktur menu, hak approval, dan pembatasan data harus mengikuti `\App\Navigation\MenuRegistry` dan permission backend.
3. **Tidak Boleh Menyeragamkan Semua Pengguna:**
   - **Teknisi Lapangan:** Berfokus pada SPK (*Work Order*), absensi shift, wizard instalasi, scan barcode ONT, redaman ODP, material custody di tangan, dan diagnostik TR-069.
   - **Mitra Reseller:** Berfokus pada pengelolaan Pelanggan miliknya (CRM), pasang baru reseller, data tagihan & pembayaran pelanggan, saldo deposit/topup, permintaan dropcore/material, dan diagnostik ONT khusus pelanggannya.
   - **Admin Gudang:** Berfokus pada katalog master barang, barang masuk (*inbound*), persetujuan permintaan barang (*requisition approval*), pengeluaran (*dispatch*), serah terima custody, retur barang rusak, dan stok opname. Tidak boleh disajikan tombol teknis pasang/swap ONT lapangan.
   - **NOC:** Berfokus pada monitoring router core, OLT, alarm link down/LOS, diagnostik optik redaman massal, dan tiket gangguan.
   - **Manager:** Pengawasan operasional cabang, approval SPK teknisi, approval pengeluaran material, rekap presensi tim cabang, dan laporan KPI cabang. Jika memiliki `job_function: TECHNICIAN`, akun tersebut berhak mengakses alur kerja teknisi lapangan.
   - **Administrator:** Akses penuh ke seluruh konfigurasi, user management, dan audit log.

---

## 2. Matriks Pemetaan Lengkap (Web vs Android)

### Kategori Status:
- `MATCH`: Sudah sesuai antara Web dan Android.
- `MISMATCH`: Ada di Web dan Android, tetapi alur/tampilan/role-nya belum sinkron.
- `MISSING`: Fitur penting di Web yang belum tersedia di Android.
- `UNAUTHORIZED`: Android menampilkan tombol/menu yang sebenarnya dilarang bagi role tersebut.
- `REDUNDANT`: Menu Android duplikat atau tidak diperlukan.

---

### 2.1 Portal Teknisi (`/technician-portal`)
**Akses:** `role: technician, manager, administrator` + `job_function: TECHNICIAN`

| Portal Web | Role | Job Function | Menu Web | Route Web | Permission Backend | Halaman / Komponen Android | Status | Tindakan Perbaikan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Technician** | Teknisi / Manager | TECHNICIAN | Overview Dashboard | `technician.dashboard` | `technician.portal` | `layoutDashboard` (Hero & KPI) | `MATCH` | Pertahankan; pastikan KPI cabang terisolasi. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Absensi Shift | `technician.attendance` | `workforce.attendance.execute` | `showAttendanceDialog()` | `MATCH` | Terintegrasi GPS & Shift. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Daftar Semua Pekerjaan | `technician.my-jobs.index` | `workforce.jobs.view` | `WorkOrdersBottomSheet` | `MATCH` | Tab status SPK sudah lengkap. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Pemasangan Baru (PSB) | `technician.my-jobs.psb` | `workforce.jobs.view` | `WorkOrdersBottomSheet` (Filter PSB) | `MISMATCH` | Filter kategori WO perlu tombol cepat dedicated. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Jadwal Maintenance | `technician.my-jobs.maintenance` | `workforce.jobs.view` | `WorkOrdersBottomSheet` (Filter Mnt) | `MISMATCH` | Perlu tab terpisah antara PSB & Maintenance. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Tiket Gangguan Lapangan | `technician.my-jobs.troubleshooting`| `ticket.view` | Dialog Tiket | `MISMATCH` | Jadikan satu dalam modul WO/Troubleshooting. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Wizard Instalasi | `technician.installation.wizard` | `onu.view` | `ProvisionOntBottomSheet` | `MATCH` | Registrasi SN, ODP, aktivasi PPPoE. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Scan Barcode ONU | `technician.installation.scan` | `onu.view` | Camera Barcode Scanner | `MATCH` | Terintegrasi input SN otomatis. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Swap ONT | `technician.swap-ont.index` | `onu.view` | `showSwapOntDialog()` | `MATCH` | Migrasi SN modem lama ke baru. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Dismantle ONT | `technician.dismantle.index`| `onu.view` | `showDismantleDialog()` | `MATCH` | Penarikan modem dan kabel. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Pencarian ODP & Maps | `technician.odp.search` / `nearest` | `odp.view`, `gis.view` | `NetworkMapActivity` | `MATCH` | Peta GIS & ODP terdekat. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Permintaan Material | `inventory.requisitions.index` | `inventory.requisition` | `InventoryBottomSheet` (Tab Minta) | `MATCH` | Pengajuan material ke gudang. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Stok di Tangan (Custody) | `inventory.custodies.index` | `inventory.custody` | `InventoryBottomSheet` (Tab Custody) | `MATCH` | Pemantauan material fisik di tangan. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Catat Pemakaian Lapangan| `inventory.usage.index` | `inventory.usage` | Form Selesai SPK (Material Usages) | `MATCH` | Input meteran kabel & aksesoris. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Retur Material Rusak | `inventory.returns.index` | `inventory.return` | `InventoryBottomSheet` (Tab Retur) | `MATCH` | Pengembalian material sisa/rusak. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Slip Gaji Teknisi | `technician.payroll.index` | `finance.view` | `showPayrollDialog()` | `MATCH` | Rincian insentif dan gaji. |
| **Technician** | Teknisi / Manager | TECHNICIAN | Popup Dialog Global | - | - | `AlertDialog showServerMenu()` | `REDUNDANT` | **Hapus AlertDialog jadul.** Ganti dengan Bottom Sheet Profil Modern. |

---

### 2.2 Portal Reseller (`/reseller-portal`)
**Akses:** `role: reseller` atau staf mitra reseller

| Portal Web | Role | Job Function | Menu Web | Route Web | Permission Backend | Halaman / Komponen Android | Status | Tindakan Perbaikan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Reseller** | Reseller | RESELLER | Dashboard Reseller | `reseller-portal.dashboard` | `reseller.view` | `layoutDashboard` (Scoped Reseller) | `MISMATCH` | Tampilkan saldo deposit & total pelanggan reseller. |
| **Reseller** | Reseller | RESELLER | Data Pelanggan CRM | `reseller-portal.customers.index`| `customer.view` | `showCustomersDialog()` | `MATCH` | Scoped via `ResellerScopeHelper`. |
| **Reseller** | Reseller | RESELLER | Tambah Pelanggan Baru | `reseller-portal.customers.create`| `customer.create` | Form Tambah Pelanggan | `MATCH` | Pasang baru pelanggan reseller. |
| **Reseller** | Reseller | RESELLER | Riwayat Tagihan Pelanggan | `reseller-portal.billing.invoices`| `invoice.view` | - | `MISSING` | Tambahkan tab Tagihan di menu Reseller. |
| **Reseller** | Reseller | RESELLER | Terima Pembayaran / Bayar | `reseller-portal.billing.payments`| `payment.view` | - | `MISSING` | Sediakan konfirmasi pembayaran pelanggan. |
| **Reseller** | Reseller | RESELLER | Saldo Deposit & Topup | `reseller-portal.finance.balance`| `finance.view`, `settlement` | - | `MISSING` | Tampilkan kartu saldo & riwayat topup reseller. |
| **Reseller** | Reseller | RESELLER | Peta ODP Wilayah Reseller| `reseller-portal.network.map` | `gis.view` | `NetworkMapActivity` | `MATCH` | Scoped ODP di area reseller. |
| **Reseller** | Reseller | RESELLER | Data Modem / ONT Reseller| `reseller-portal.network.onus.index`| `onu.view` | `layoutDevices` | `MATCH` | Scoped hanya modem pelanggan milik reseller. |
| **Reseller** | Reseller | RESELLER | Permintaan Dropcore/Kabel | `inventory.requisitions.index` | `inventory.requisition` | `InventoryBottomSheet` | `MATCH` | Permintaan barang mitra ke gudang ISP. |
| **Reseller** | Reseller | RESELLER | Stok Dropcore Reseller | `inventory.custodies.index` | `inventory.custody` | `InventoryBottomSheet` | `MATCH` | Stok barang yang dipegang reseller. |
| **Reseller** | Reseller | RESELLER | Swap / Ganti Modem Sendiri | - | - | Tombol `btnActionSwap` | `UNAUTHORIZED` | Reseller dilarang swap bebas tanpa tiket resmi. |
| **Reseller** | Reseller | RESELLER | Dismantle / Penarikan ONT | - | - | Tombol `btnActionDismantle` | `UNAUTHORIZED` | Dismantle aset ISP bukan wewenang bebas reseller. |
| **Reseller** | Reseller | RESELLER | SPK Lapangan Pusat | `support.work-orders.*` | `workforce.jobs.view` | Tab SPK di Bottom Navigation | `UNAUTHORIZED` | **Sudah diperbaiki**: Tab 2 otomatis berganti jadi "Pelanggan". |

---

### 2.3 Portal Admin Gudang & Logistik (`/inventory/*`)
**Akses:** `role: admin_gudang`, atau `job_title` mengandung "gudang"/"logistik"

| Portal Web | Role | Job Function | Menu Web | Route Web | Permission Backend | Halaman / Komponen Android | Status | Tindakan Perbaikan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Gudang** | Admin Gudang | WAREHOUSE | Dashboard Gudang | `inventory.dashboard` | `inventory.view` | `layoutDashboard` (Tampilan Default) | `MISMATCH` | Tampilkan KPI Gudang: Permintaan Pending, Inbound, Retur. |
| **Gudang** | Admin Gudang | WAREHOUSE | Master Barang | `inventory.items.index` | `inventory.view` | `InventoryBottomSheet` | `MATCH` | Katalog stok material & serial number. |
| **Gudang** | Admin Gudang | WAREHOUSE | Data Gudang / Lokasi | `inventory.warehouses.index` | `warehouse.view` | - | `MISSING` | Tambahkan filter pemilihan gudang cabang. |
| **Gudang** | Admin Gudang | WAREHOUSE | Barang Masuk (Inbound) | `inventory.inbounds.index` | `inventory.inbound` | - | `MISSING` | Sediakan tombol input Inbound dari vendor. |
| **Gudang** | Admin Gudang | WAREHOUSE | Approval Permintaan Barang| `inventory.requisitions.index` | `inventory.requisition` | `InventoryBottomSheet` (Tab Requisition)| `MISMATCH` | Gudang butuh tombol **APPROVE / REJECT** permintaan. |
| **Gudang** | Admin Gudang | WAREHOUSE | Barang Keluar (Dispatch) | `inventory.dispatches.index` | `inventory.dispatch` | - | `MISSING` | Pengeluaran barang ke teknisi/reseller. |
| **Gudang** | Admin Gudang | WAREHOUSE | Rekap Custody Semua Teknisi | `inventory.custodies.index` | `inventory.custody` | `InventoryBottomSheet` (Tab Custody) | `MISMATCH` | Gudang harus melihat seluruh teknisi, bukan hanya diri sendiri. |
| **Gudang** | Admin Gudang | WAREHOUSE | Retur & Pengembalian Rusak | `inventory.returns.index` | `inventory.return` | `InventoryBottomSheet` (Tab Retur) | `MATCH` | Penerimaan barang retur dari teknisi. |
| **Gudang** | Admin Gudang | WAREHOUSE | Stok Opname | `inventory.opnames.index` | `inventory.opname` | - | `MISSING` | Checklist fisik stok opname berkala. |
| **Gudang** | Admin Gudang | WAREHOUSE | Kartu Stok Mutasi | `inventory.stock-card.index` | `inventory.stock-card` | - | `MISSING` | Riwayat keluar masuk per item. |
| **Gudang** | Admin Gudang | WAREHOUSE | Pasang ONT Lapangan | - | - | Tombol `btnActionProvision` | `UNAUTHORIZED` | **Sembunyikan dari Admin Gudang.** |
| **Gudang** | Admin Gudang | WAREHOUSE | Swap ONT Lapangan | - | - | Tombol `btnActionSwap` | `UNAUTHORIZED` | **Sembunyikan dari Admin Gudang.** |
| **Gudang** | Admin Gudang | WAREHOUSE | Dismantle Lapangan | - | - | Tombol `btnActionDismantle` | `UNAUTHORIZED` | **Sembunyikan dari Admin Gudang.** |
| **Gudang** | Admin Gudang | WAREHOUSE | Tambah Pelanggan Baru | - | - | Tombol `btnActionCustomers` | `UNAUTHORIZED` | **Sembunyikan dari Admin Gudang.** |
| **Gudang** | Admin Gudang | WAREHOUSE | Akses Perangkat TR-069 | - | - | Tab `Perangkat` Bottom Nav | `UNAUTHORIZED` | **Sembunyikan / Nonaktifkan untuk Gudang.** |

---

### 2.4 Portal NOC & Monitoring Jaringan (`/noc/*`)
**Akses:** `role: noc`, `job_function: NOC`

| Portal Web | Role | Job Function | Menu Web | Route Web | Permission Backend | Halaman / Komponen Android | Status | Tindakan Perbaikan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **NOC** | NOC Operator | NOC | Dashboard NOC | `noc.overview` | `noc.view` | `layoutDashboard` (Tampilan Default) | `MISMATCH` | Tampilkan KPI Jaringan: Total Router, OLT, Alarm LOS, ONU Kritis. |
| **NOC** | NOC Operator | NOC | Router / MikroTik Monitor | `noc.routers.index` | `noc.router.view` | - | `MISSING` | Tampilkan status CPU & Link Router. |
| **NOC** | NOC Operator | NOC | Monitoring OLT & PON | `noc.olts.index` | `noc.olt.view` | - | `MISSING` | Tampilkan status OLT & port PON online/down. |
| **NOC** | NOC Operator | NOC | Monitoring GenieACS (TR-069)| `noc.acs.devices.index` | `noc.onu.view` | `layoutDevices` | `MATCH` | Live filter Online, Offline, Redaman Kritis. |
| **NOC** | NOC Operator | NOC | Diagnostik & Reboot Remote | `noc.onu.manage` | `noc.onu.manage` | `DeviceDetailActivity` | `MATCH` | Diagnostik Rx/Tx dBm, Reboot, Ubah SSID/PW. |
| **NOC** | NOC Operator | NOC | Alarm & Incident Events | `noc.alarms.index` | `noc.alarms.view` | `NotificationsBottomSheet` | `MATCH` | Notifikasi alarm fiber cut atau redaman drop. |
| **NOC** | NOC Operator | NOC | Peta Topologi & GIS | `noc.topology.index`, `gis.map` | `gis.view` | `NetworkMapActivity` | `MATCH` | Peta ODP, ODC, dan jalur kabel fiber. |
| **NOC** | NOC Operator | NOC | Pasang Dropcore Fisik | - | - | Tombol `btnActionProvision` | `MISMATCH` | Ganti fokus ke aktivasi remote & diagnostik. |
| **NOC** | NOC Operator | NOC | Permintaan Barang Gudang | - | - | Tombol `btnActionInventory` | `UNAUTHORIZED` | NOC tidak mengelola stok fisik harian di motor. |

---

### 2.5 Administrator & Manager Pusat (`/dashboard`, `/keuangan/*`)
**Akses:** `role: administrator, manager`

| Portal Web | Role | Job Function | Menu Web | Route Web | Permission Backend | Halaman / Komponen Android | Status | Tindakan Perbaikan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Dashboard Eksekutif | `dashboard` | `dashboard.view` | `layoutDashboard` | `MATCH` | Menampilkan ringkasan global/cabang. |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Approval SPK Selesai | `support.work-orders.*` | `workforce.approve` | `WorkOrdersBottomSheet` | `MISMATCH` | Tambahkan tombol **APPROVE WO** untuk Manager. |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Approval Permintaan Material| `inventory.requisitions.*` | `inventory.manage` | `InventoryBottomSheet` | `MISMATCH` | Tambahkan tombol **APPROVE REQUISITION**. |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Monitoring Presensi Tim | `admin.attendance.index` | `workforce.view` | `AttendanceDialog` | `MISMATCH` | Sediakan tab monitoring presensi anak buah. |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Laporan KPI & SLA | `admin.reports.*` | `report.view` | `OperationalReportsBottomSheet` | `MATCH` | Menampilkan performa teknisi & durasi WO. |
| **Admin/Mgr** | Administrator / Manager | MANAGEMENT | Bulk ACS Configuration | `noc.provisioning.*` | `acs.manage` | `BulkAdminBottomSheet` | `MATCH` | Konfigurasi massal WAN/WiFi ONT. |

---

## 3. Rencana Tindakan Perbaikan Berdasarkan Hasil Audit (Action Plan)

### Langkah 1: Hapus Dialog Jadul `showServerMenu()` (Penyebab Masalah pada Screenshot)
- Pada screenshot BlueStacks, dialog daftar teks standar `AlertDialog` muncul menutupi dashboard dengan 17 menu tanpa filter permission.
- **Solusi:** Gantikan `showServerMenu()` dengan **`ProfileAndSettingsBottomSheet`** modern Material Design 3 yang hanya menampilkan:
  1. Identitas Akun, Avatar, Role, Nama Cabang/Tenant.
  2. Shortcut Fitur Sesuai Permission Pengguna (tidak menampilkan aksi yang dilarang).
  3. Ganti Tema (Dark/Light).
  4. Ganti Bahasa.
  5. Cek Pembaruan Aplikasi (OTA).
  6. Tombol Keluar Akun (*Logout*).

### Langkah 2: Dinamisasi Grid 12 Quick Actions di Dashboard
Alih-alih menampilkan 12 tombol yang sama persis untuk semua role:
- **Jika TEKNISI / MANAGER TECHNICIAN:** Tampilkan tombol lapangan (*Absensi, SPK Saya, Pasang Baru, Swap ONT, Dismantle, Stok di Tangan, Peta ODP, Slip Gaji*).
- **Jika RESELLER:** Tampilkan tombol reseller (*Pelanggan Saya, Tambah Pelanggan, Tagihan Pelanggan, Saldo Deposit/Topup, Peta ODP Reseller, Minta Dropcore, Diagnostik Modem Pelanggan*).
- **Jika ADMIN GUDANG:** Tampilkan tombol pergudangan (*Barang Masuk, Approval Permintaan, Barang Keluar/Dispatch, Stok Custody Teknisi, Retur Barang Rusak, Stok Opname, Absensi*). Sembunyikan Pasang/Swap ONT!
- **Jika NOC:** Tampilkan tombol jaringan (*Router Monitor, OLT Monitor, Live ONT Kritis, Alarm Link Down, Peta Topologi, Tiket Gangguan*). Sembunyikan form pemasangan fisik!

### Langkah 3: Standardisasi Bottom Navigation Bar
- Tab 1: **Beranda** (Tetap untuk semua role).
- Tab 2: **Tugas Utama**:
  - Teknisi/Manager: **SPK Lapangan**.
  - Reseller: **Pelanggan CRM**.
  - Admin Gudang: **Permintaan Barang (Requisitions)**.
  - NOC: **Alarm & Tiket**.
- Tab 3: **Logistik & Material**:
  - Teknisi/Reseller: **Stok di Tangan & Minta Barang**.
  - Admin Gudang: **Katalog & Gudang Logistik**.
  - NOC: **Aset Jaringan**.
- Tab 4: **Jaringan & Perangkat**:
  - Teknisi/NOC: **GenieACS TR-069 Devices**.
  - Reseller: **Modem Pelanggan Reseller**.
  - Admin Gudang: Dinonaktifkan atau diganti dengan **Stok Opname**.
- Tab 5: **Lainnya**: Membuka Bottom Sheet Akun & Pengaturan M3.

---

## 4. Kesimpulan Audit Pemetaan

Matriks ini membuktikan bahwa:
1. **Backend dsBilling telah memiliki sistem role & permission yang sangat kaya dan granular** (`UserRole`, `UserPermission`, dan `MenuRegistry`).
2. **Kelemahan aplikasi Android sebelumnya adalah menyeragamkan menu untuk semua user** (terutama popup dialog `showServerMenu()` dan grid 12 Quick Actions).
3. Dengan menerapkan pemetaan dinamis di atas, aplikasi Android akan **100% selaras dengan dsBilling Web**:
   - Reseller hanya melihat urusan reseller.
   - Admin Gudang fokus ke stok dan approval.
   - Teknisi fokus ke pekerjaan lapangan.
   - NOC fokus ke monitoring dan alarm.
   - Tidak ada lagi popup menu yang menampilkan tindakan yang tidak diizinkan (*Zero Unauthorized Actions*).
