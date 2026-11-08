# LAPORAN IMPLEMENTASI: PHASE 2D — DEVICE & NETWORK OPERATIONS
## dsBilling Staff — ISP Field Operations & Network Management

Dokumen ini mencatat penyelesaian dan hasil pengujian implementasi **PHASE 2D: Device & Network Operations** pada backend Laravel **dsBilling** (`D:\dsBilling`) dan aplikasi native Android **dsBilling Staff** (`D:\dsbilling-mobile`).

---

## 1. Ringkasan Eksekusi Subfase 2D

### A. Backend dsBilling (`D:\dsBilling`)
1. **Dedicated Network & Device Mobile Controller**:
   - Dibuat controller [`MobileNetworkApiController.php`](file:///D:/dsBilling/app/Http/Controllers/Api/Mobile/MobileNetworkApiController.php) dengan 5 endpoint REST API:
     - `GET /api/mobile/network/devices`: Pencarian perangkat ONT/ONU berdasarkan Serial Number, MAC Address, Model, ODP, atau Nama Pelanggan.
     - `GET /api/mobile/network/devices/{identifier}`: Menampilkan detail perangkat berdasarkan SN atau ID, termasuk status ODP dan layanan pelanggan.
     - `GET /api/mobile/network/odps`: Katalog Titik Distribusi ODP dengan sisa port tersedia (`available_ports`), serta perhitungan jarak radius GPS (Haversine formula) terdekat dari koordinat teknisi.
     - `POST /api/mobile/network/devices/provision-ont`: Pairing & provisioning ONT secara atomik di database dsBilling:
       - Memvalidasi dan menyimpan nomor seri ONT (*uppercase*), MAC, model, dan status `active` serta `provision_status = 'provisioned'`.
       - Menghubungkan ONT ke ODP yang dipilih dan memperbarui alokasi port ODP.
       - Menghubungkan ONT ke layanan pelanggan (`CustomerService`).
       - Memperbarui Work Order (SPK) terkait dengan riwayat pemasangan ONT dan otomatis memajukan status menjadi `in_progress`.
     - `GET /api/mobile/network/devices/{serialNumber}/diagnostics`: Diagnostik sinyal optik real-time (RX/TX Power dBm), evaluasi kualitas redaman (*Optimal, Waspada, Loss*), status online/offline, dan waktu inform terakhir.
2. **Pendaftaran Rute REST API**:
   - Rute didaftarkan di [`routes/api.php`](file:///D:/dsBilling/routes/api.php) di bawah middleware `auth:sanctum` dengan prefiks `/api/mobile/network/*`.

### B. Aplikasi Android dsBilling Staff (`D:\dsbilling-mobile`)
1. **Model Data Jaringan & Perangkat**:
   - [`DsBillingModels.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs\client\data\model\DsBillingModels.kt): Ditambahkan model `DsBillingOnuDevice`, `DsBillingOdp`, `DsBillingProvisionResult`, `DsBillingOdpInfo`, `DsBillingCustomerInfo`, `DsBillingWorkOrderInfo`, dan `DsBillingDeviceDiagnostics`.
2. **Layanan Jaringan API**:
   - [`DsBillingApiService.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/network/DsBillingApiService.kt): Dilengkapi dengan method `searchDevices()`, `getDeviceBySn()`, `getOdps()`, `provisionOnt()`, dan `getDeviceDiagnostics()`.
3. **Antarmuka Pemasangan & Barcode Scanner ONT**:
   - [`bottom_sheet_provision_ont.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/bottom_sheet_provision_ont.xml) & [`OntProvisioningBottomSheet.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/dialog/OntProvisioningBottomSheet.kt):
     - **Scan Barcode & Tempel SN**: Tombol pemindai barcode kamera sistem (ZXing/ML-Kit intent) dan tombol tempel clipboard untuk input instan tanpa salah ketik.
     - **Pilihan ODP & Port**: Dialog pemilihan ODP terdekat dengan indikator sisa port tersedia (`[ODP-KST-01] Malang Indah - 11/16 Port Tersedia`).
     - **Pengaturan Wi-Fi Rumah Pelanggan**: Konfigurasi otomatis nama SSID (`NamaPelanggan_WiFi`) dan sandi default.
     - **Tombol Provisioning**: `⚡ Pasang & Provisioning ONT` dengan proses atomik ke backend.
4. **Antarmuka Diagnostik & Remote Kontrol TR-069**:
   - [`bottom_sheet_device_diagnostics.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/bottom_sheet_device_diagnostics.xml) & [`DeviceDiagnosticsBottomSheet.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/dialog/DeviceDiagnosticsBottomSheet.kt):
     - Tampilan badge Online/Offline, redaman RX Power dBm, TX Power dBm, ODP terhubung, dan SSID Wi-Fi aktif.
     - Tombol cepat **Summon / Inform** (TR-069 live inform via backend proxy).
     - Tombol cepat **Reboot ONT** (remote restart modem dari aplikasi).
     - Tombol navigasi **Detail TR-069** menuju [`DeviceDetailActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/DeviceDetailActivity.kt).
5. **Integrasi Navigasi SPK & Menu Utama**:
   - [`WorkOrdersBottomSheet.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/dialog/WorkOrdersBottomSheet.kt): Tombol `🔍 Modem` di kartu SPK kini memiliki opsi pintar:
     - Jika pelanggan sudah memiliki ONT SN: Membuka `DeviceDiagnosticsBottomSheet` (redaman + reboot + summon).
     - Jika pelanggan belum memiliki ONT: Membuka `OntProvisioningBottomSheet` untuk scan barcode & pasang modem.
     - Opsi alternatif untuk mencari di daftar perangkat GenieACS.
   - [`MainActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/MainActivity.kt): Menu utama ditambahkan pintasan `"📷 Scan & Provisioning ONT"` dan `"🗺️ Peta ODP & Jaringan (GIS)"`.

---

## 2. Hasil Pengujian Otomatis

### A. Pengujian Modul Network Operations PHPUnit (`tests/Feature/MobileNetworkOperationsTest.php`)
```bash
   PASS  Tests\Feature\MobileNetworkOperationsTest
  ✓ technician can search and list devices
  ✓ technician can get device by serial number
  ✓ technician can search and list odps
  ✓ technician can provision ont and bind to work order
  ✓ technician can query device diagnostics
  ✓ unauthenticated request is rejected

  Tests:    6 passed (22 assertions)
  Duration: 6.17s
```

### B. Pengujian Regresi Lengkap Backend (5 Test Suite: 35 Tests)
```bash
   PASS  Tests\Feature\MobileApiAuthTest (8 tests, 20 assertions)
   PASS  Tests\Feature\MobileWorkforceTest (8 tests, 22 assertions)
   PASS  Tests\Feature\MobileInventoryTest (8 tests, 25 assertions)
   PASS  Tests\Feature\MobileWorkEvidenceTest (5 tests, 20 assertions)
   PASS  Tests\Feature\MobileNetworkOperationsTest (6 tests, 22 assertions)

   Tests:    35 passed (107 assertions)
   Time:     00:08.428
   Status:   100% PASS (Zero Failure)
```

### C. Kompilasi APK Android Debug dsBilling Staff
```bash
BUILD SUCCESSFUL in 41s
37 actionable tasks: 15 executed, 22 up-to-date
Output: D:\dsbilling-mobile\app\build\outputs\apk\debug\app-debug.apk (8.27 MB)
```

---

## 3. Status Roadmap Milestone Phase 2

| Subfase | Cakupan Fitur | Status |
|---|---|---|
| **Phase 2A** | Work Order & Operational Workflow (List, filter, assign, approval, WA) | ✅ Selesai & Terverifikasi |
| **Phase 2B** | Inventory & Material Management (Custody stok teknisi, transfer, retur, request) | ✅ Selesai & Terverifikasi |
| **Phase 2C** | Field Service & Work Evidence (Checklist, OPM redaman, foto bukti, signature) | ✅ Selesai & Terverifikasi |
| **Phase 2D** | Device & Network Operations (Scan Barcode/QR ONT, TR-069 ACS Provisioning, ODP) | ✅ Selesai & Terverifikasi |
| **Phase 2E** | Notification, Offline & Synchronization (Room DB, offline queue, auto-sync) | ⏳ Siap Dimulai |
| **Phase 2F** | Reporting & Operational History (Log aktivitas lapangan, summary KPI teknisi) | ⏳ Antrean |
