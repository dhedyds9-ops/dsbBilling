# SSOT: ARSITEKTUR INFORMASI & NAVIGASI TERPADU (MOBILE & WEB PORTAL)
## dsBilling Enterprise & dsBilling Mobile v1.1.27 Build 10127

---

## 1. PENDAHULUAN & PRINSIP UTAMA

Dokumen ini merupakan **Single Source of Truth (SSOT)** arsitektur informasi, taksonomi menu, kamus istilah bisnis, dan pemetaan navigasi antara **dsBilling Web Portal** (`D:\dsBilling`) dan **dsBilling Mobile** (`D:\dsbilling-mobile`).

### Prinsip Harmonisasi Navigasi
1. **Satu Istilah Bisnis Baku (Unified Business Glossary):** Menghilangkan multi-penamaan untuk satu entitas bisnis yang sama (misal: "Inventory", "Stok", "Gudang Saya" distandardisasi menjadi **Stok Tangan** untuk teknisi dan **Stok Gudang** untuk logistik).
2. **Model 2-Pilar Tugas Lapangan (Two-Pillar Work Order Model):** 
   - **Tugas Instalasi (PSB):** Pemasangan pelanggan baru, aktivasi ONT, registrasi PPPoE/Hotspot, dan verifikasi redaman optik.
   - **Tiket Gangguan:** Penanganan keluhan, pemulihan layanan, perbaikan kabel dropcore, atau penggantian ONT rusak.
   - Keduanya bernaung di bawah payung modul tunggal: **Tugas Lapangan** (`WorkOrder`).
3. **Pemisahan Peran Platform yang Jelas (Platform Role Scoping):**
   - **dsBilling Mobile** ditujukan khusus untuk **Field Force, Staff Operasional, NOC, dan Reseller**.
   - **Pelanggan Umum (Customer)** diarahkan ke **dsBilling Web Portal (`dsBilling Connect`)** via responsive SSO web browser untuk keamanan data tagihan, pembayaran payment gateway, dan privasi akun.
4. **Zero Regression Contract:** Perubahan label antarmuka tidak mengubah struktur kontrak API JSON (`MobileWorkOrderApiController`, `MobileSyncApiController`, dsb.) dan skema database Laravel.

---

## 2. KAMUS ISTILAH BISNIS RESMI (OFFICIAL BUSINESS GLOSSARY)

| Modul / Istilah Resmi | Definisi & Fungsi Bisnis | Padanan Web Portal | Padanan Android Mobile |
|---|---|---|---|
| **Ringkasan** | Dashboard utama penyaji ringkasan KPI, kuota penugasan, dan statistik operasional harian. | `Ringkasan` / `Dashboard` | `Ringkasan` / Header `MainActivity` |
| **Tugas Lapangan** | Seluruh surat perintah kerja (SPK) teknisi yang dieksekusi di lapangan. | `Tugas Lapangan` (`technician.my-jobs.index`) | `Tugas Lapangan` (`WorkOrderActivity`) |
| **Tugas Instalasi (PSB)** | SPK khusus pemasangan baru pelanggan internet (FTTH/Wireless). | `Tugas Instalasi (PSB)` (`technician.installation.wizard`) | `Instalasi (PSB)` (Tab & Quick Action) |
| **Tiket Gangguan** | Laporan gangguan atau keluhan pelanggan yang memerlukan investigasi & resolusi. | `Tiket Gangguan` (`technician.my-jobs.index?tab=tickets`) | `Tiket Gangguan` (`TicketsActivity` / Tab) |
| **Pemeliharaan** | Pekerjaan rutin berkala, maintenance kabel OSP, dan perapian jalur kabel. | `Pemeliharaan` (`technician.my-jobs.index?type=maintenance`) | `Pemeliharaan` (Quick Action) |
| **Ganti ONT** | Penggantian perangkat ONT pelanggan lama ke serial baru. | `Ganti ONT` (`technician.swap-ont.index`) | `Ganti ONT` (`OntSwapActivity`) |
| **Cabut ONT** | Dismantle / penarikan perangkat ONT dari pelanggan non-aktif. | `Cabut ONT` (`technician.dismantle.index`) | `Cabut ONT` (`OntDismantleActivity`) |
| **Pelanggan** | Master database pelanggan aktif, terisolir, dan riwayat langganan. | `Pelanggan` (`customers.index`) | `Pelanggan` (`CustomerActivity`) |
| **Perangkat** | Modul manajemen perangkat jaringan fisik & virtual (OLT, MikroTik, Switch). | `Perangkat` (`devices.index`) | `Perangkat ONT` (`DevicesActivity`) |
| **GenieACS** | Sub-modul TR-069 Auto-Configuration Server untuk manajemen CPE/ONT. | `GenieACS` (`admin.genieacs.index`) | `Diagnosa ONT` (`OntDiagnosticActivity`) |
| **Peta Jaringan** | Visualisasi geografis ODP, ODC, rute fiber, dan lokasi pelanggan. | `Peta Jaringan` (`admin.coverage.index`) | `Peta Jaringan` (`NetworkMapActivity`) |
| **Sensus Area** | Audit lapangan untuk mencocokkan data pelanggan, ODP, dan redaman. | `Sensus Area` (`technician.audit.form`) | `Sensus Area` (`NetworkAuditActivity`) |
| **Gudang** | Manajemen fisik gudang penyimpanan barang di tingkat pusat & cabang. | `Gudang` (`warehouse.index`) | N/A (Admin Web Only) |
| **Katalog Material** | Master data seluruh item suku cadang, kabel, dropcore, adaptor, dan ONT. | `Katalog Material` (`admin.inventory.items`) | `Katalog Material` (`InventoryActivity` Tab 2) |
| **Stok Gudang** | Kuantitas fisik material yang tersimpan di dalam gudang cabang/pusat. | `Stok Gudang` (`warehouse.stock`) | N/A (Admin Web Only) |
| **Stok Tangan** | Material yang sedang dipegang dan menjadi tanggung jawab (custody) teknisi. | `Stok Tangan` (`technician.material.index`) | `Stok Tangan` (`InventoryActivity` Tab 1) |
| **Permintaan Material** | Pengajuan permohonan pengambilan material ke gudang cabang. | `Permintaan Material` (`technician.material.requisition`) | `Permintaan Material` (Modal Form) |
| **Serah Terima Material**| Alur penyerahan fisik barang dari gudang ke teknisi / antar teknisi. | `Serah Terima Material` (`inventory.transfers`) | `Transfer Material` (`InventoryActivity`) |
| **Penggunaan Material** | Pencatatan konsumsi material pada SPK tertentu. | `Penggunaan Material` (`technician.material.usage`) | `Penggunaan Material` (`WorkOrderDetailActivity`) |
| **Pengembalian Material**| Pengembalian material sisa atau bekas pakai kembali ke gudang. | `Pengembalian Material` (`technician.material.return`) | `Pengembalian Material` (Modal Form) |
| **Absensi** | Pencatatan kehadiran harian teknisi (Clock-in, Clock-out, Geotag). | `Absensi` (`technician.attendance`) | `Absensi` (`AttendanceActivity`) |
| **Keuangan** | Invoice, transaksi kas, mutasi pembayaran, dan komisi reseller. | `Keuangan` (`billing.invoices.index`) | `Keuangan` (`ResellerCommissionActivity`) |
| **Slip Gaji** | Informasi rincian honor instalasi, gaji, dan insentif teknisi. | `Slip Gaji` (`technician.payroll.index`) | `Slip Gaji` (`PayrollActivity`) |
| **Pengaturan** | Konfigurasi sistem, tenant, hak akses, dan profil pengguna. | `Pengaturan` (`settings.index`) | `Pengaturan` (`SettingsActivity`) |

---

## 3. MATRIKS ROLE & PERMISSION (10 KONTEKS)

| No | Konteks Pengguna | Lingkup Platform | Modul yang Dapat Diakses | Batasan Otorisasi (Boundary) |
|---|---|---|---|---|
| 1 | **Android Teknisi** | Mobile App | Ringkasan, Tugas Lapangan (Instalasi & Gangguan), Ganti ONT, Cabut ONT, Diagnosa ONT, Stok Tangan, Sensus Area, Peta Jaringan, Absensi, Slip Gaji. | Dibatasi ke `branch_id` dan SPK yang di-assign (`assigned_to`). |
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

## 4. PEMETAAN MENU TERPADU (MAPPING TABLE)

| Modul Resmi | Rute Web Portal (Laravel) | Activity / View Android | Backend Controller | Model SSOT | Endpoint Mobile API |
|---|---|---|---|---|---|
| **Ringkasan** | `/dashboard`, `/technician/dashboard` | `MainActivity.kt` | `DashboardController`, `MobileDashboardApiController` | User, WorkOrder, Ticket | `GET /api/mobile/v1/dashboard/summary` |
| **Tugas Lapangan** | `/technician/my-jobs` | `WorkOrderActivity.kt` | `MobileWorkOrderApiController` | `WorkOrder` | `GET /api/mobile/v1/work-orders` |
| **Tugas Instalasi (PSB)** | `/technician/installation/wizard` | `WorkOrderDetailActivity.kt` (Type: PSB) | `MobileWorkOrderApiController`, `MobileNetworkApiController` | `WorkOrder`, `Customer`, `ServiceInstance` | `POST /api/mobile/v1/network/provision-psb` |
| **Tiket Gangguan** | `/technician/my-jobs?tab=tickets` | `TicketsActivity.kt` | `MobileWorkOrderApiController` | `Ticket`, `WorkOrder` | `GET /api/mobile/v1/tickets` |
| **Ganti ONT** | `/technician/swap-ont` | `OntSwapActivity.kt` | `MobileNetworkApiController` | `OntDevice`, `StockMutation` | `POST /api/mobile/v1/network/swap-ont` |
| **Cabut ONT** | `/technician/dismantle` | `OntDismantleActivity.kt` | `MobileNetworkApiController` | `OntDevice`, `StockMutation` | `POST /api/mobile/v1/network/dismantle-ont` |
| **Diagnosa ONT** | `/technician/diagnostic` | `OntDiagnosticActivity.kt` | `MobileNetworkApiController` | `GenieAcsDevice` | `POST /api/mobile/v1/network/diagnose-ont` |
| **Pelanggan** | `/customers` | `CustomerActivity.kt` | `MobileCustomerApiController` | `Customer` | `GET /api/mobile/v1/customers` |
| **Stok Tangan** | `/technician/material` | `InventoryActivity.kt` (Tab 1: Stok Tangan) | `MobileInventoryApiController` | `TechnicianCustody`, `StockMutation` | `GET /api/mobile/v1/inventory/my-custody` |
| **Katalog Material** | `/inventory/items` | `InventoryActivity.kt` (Tab 2: Katalog) | `MobileInventoryApiController` | `Item` | `GET /api/mobile/v1/inventory/items` |
| **Serah Terima** | `/inventory/transfers` | `InventoryActivity.kt` (Action: Transfer) | `MobileInventoryApiController` | `ItemTransfer` | `POST /api/mobile/v1/inventory/transfer` |
| **Peta Jaringan** | `/coverage/map` | `NetworkMapActivity.kt` | `MobileNetworkApiController` | `Odp`, `Odc`, `Customer` | `GET /api/mobile/v1/network/odp-nearest` |
| **Sensus Area** | `/technician/audit` | `NetworkAuditActivity.kt` | `MobileNetworkApiController` | `NetworkAudit` | `POST /api/mobile/v1/network/audit-submit` |
| **Absensi** | `/technician/attendance`| `AttendanceActivity.kt` | `MobileAttendanceApiController` | `TechnicianAttendance`| `POST /api/mobile/v1/attendance/check-in` |
| **Slip Gaji** | `/technician/payroll` | `PayrollActivity.kt` | `MobilePayrollApiController` | `TechnicianPayroll` | `GET /api/mobile/v1/payroll/my-summary` |
| **Pengaturan** | `/settings` | `SettingsActivity.kt` | `MobileProfileApiController` | `User` | `GET /api/mobile/v1/profile` |

---

## 5. SPESIFIKASI SIKLUS TUGAS LAPANGAN

### A. Siklus Permintaan PSB vs SPK Eksekusi
```
[Calon Pelanggan / Lead] 
       │ 
       ▼ (Registrasi CRM / Reseller)
[Permintaan PSB (crm.installations)] ── (Verifikasi Coveran & Dokumen)
       │
       ▼ (Admin / Dispatcher menerbitkan SPK)
[SPK Tugas Instalasi (work_orders, type: PSB)]
       │
       ▼ (Teknisi Eksekusi via Mobile Wizard)
  1. Validasi & Reservasi Serial ONT (Stok Tangan)
  2. Pemasangan Dropcore & Pencatatan Redaman Optik (dBm)
  3. Provisioning Otomatis (OLT -> GenieACS -> MikroTik PPPoE/Hotspot)
  4. Penggunaan Material (Kabel, Fast Connector, Adaptor)
  5. Tanda Tangan Digital Pelanggan
       │
       ▼
[SPK Completed -> Customer & ServiceInstance Aktif -> Faktur Pertama]
```

### B. Siklus Tiket Gangguan vs SPK Perbaikan
```
[Laporan Gangguan Pelanggan] 
       │ 
       ▼
[Tiket Gangguan (tickets)]
       │
       ├── Solved via Remote / NOC ──► [Tiket Closed]
       │
       ▼ Butuh Penanganan Fisik Lapangan (Admin / NOC Dispatch)
[SPK Perbaikan (work_orders, type: TROUBLESHOOT / TIKET)]
       │
       ▼ (Teknisi Lapangan Menangani)
  - Penggantian Kabel / Splicing
  - Ganti ONT (Swap ONT) jika perangkat rusak
  - Catat Hasil Resolusi
       │
       ▼
[SPK Completed] ──► [Tiket Gangguan Otomatis Resolved & Closed]
```

---

## 6. FITUR KHUSUS PLATFORM (PLATFORM-SPECIFIC FEATURES)

### A. Fitur Khusus Web Portal (Tidak Ada di Android Mobile)
1. **Rekonsiliasi Keuangan & Chart of Accounts:** Jurnal akuntansi pembukuan ganda (double-entry ledger) memerlukan layar lebar untuk analisis neraca, laba-rugi, dan batch reconciliation.
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
