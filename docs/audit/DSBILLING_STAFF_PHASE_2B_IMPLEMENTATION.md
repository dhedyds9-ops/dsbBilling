# LAPORAN IMPLEMENTASI: PHASE 2B — INVENTORY & MATERIAL MANAGEMENT
## dsBilling Staff — ISP Field Operations & Network Management

Dokumen ini mencatat penyelesaian dan hasil pengujian implementasi **PHASE 2B: Inventory & Material Management** pada backend Laravel **dsBilling** (`D:\dsBilling`) dan aplikasi native Android **dsBilling Staff** (`D:\dsbilling-mobile`).

---

## 1. Ringkasan Eksekusi Subfase 2B

### A. Backend dsBilling (`D:\dsBilling`)
1. **Pemanfaatan Model Inventory Relasional**:
   - Memanfaatkan model dan tabel relasi yang sudah ada di sistem dsBilling tanpa membuat database terpisah:
     - `InventoryCustody`: pelacakan barang fisik yang dipegang teknisi (*holder_id*), saldo tersisa (*available_quantity* = *quantity* - *used_quantity* - *returned_quantity*), serta status custody (`in_custody`, `handed_over`, `returned`, `lost_damaged`).
     - `InventoryRequisition` & `InventoryRequisitionItem`: pengajuan permintaan material dari teknisi ke gudang.
     - `InventoryCustodyTransfer`: pencatatan mutasi dan serah terima material antar-teknisi lapangan.
     - `InventoryReturn` & `InventoryReturnItem`: pengembalian material sisa, klaim barang rusak, atau pengujian QC gudang.
     - `Item` & `Warehouse`: katalog master barang dan gudang cabang.
2. **Dedicated Mobile Controller**:
   - Dibuat controller [`MobileInventoryApiController.php`](file:///D:/dsBilling/app/Http/Controllers/Api/Mobile/MobileInventoryApiController.php) dengan 7 endpoint REST API:
     - `GET /api/mobile/inventory/my-stock`: daftar barang dalam penguasaan teknisi yang sedang login, sisa kuantitas real-time, serial number (jika *serialized*), dan ringkasan total unit.
     - `GET /api/mobile/inventory/items`: katalog barang aktif di gudang cabang untuk permohonan material.
     - `POST /api/mobile/inventory/requisitions`: pengajuan permohonan material baru dengan nomor otomatis (`REQ-YYYYMM-XXXXX`).
     - `GET /api/mobile/inventory/requisitions`: riwayat permintaan material dan status persetujuannya.
     - `POST /api/mobile/inventory/requisitions/{id}/approve`: persetujuan permintaan material (khusus role Admin Gudang dan Manager), yang otomatis menerbitkan dan menambahkan stok custody kepada teknisi pemohon.
     - `POST /api/mobile/inventory/custody/transfer`: serah terima material antar-teknisi lapangan dengan validasi kepemilikan dan pengurangan kuantitas saldo asal secara atomik.
     - `POST /api/mobile/inventory/returns`: pengajuan retur barang ke gudang (kategori: Bagus/Sisa, Rusak/Mati Total, atau Perlu Pengecekan QC).

### B. Aplikasi Android dsBilling Staff (`D:\dsbilling-mobile`)
1. **Model Data Inventory**:
   - [`DsBillingModels.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/model/DsBillingModels.kt) dilengkapi dengan model `DsBillingCustodyItem`, `DsBillingWarehouseItem`, `DsBillingRequisition`, `DsBillingRequisitionLine`, dan `DsBillingInventorySummary`.
2. **Layanan Jaringan API**:
   - [`DsBillingApiService.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/data/network/DsBillingApiService.kt) dilengkapi dengan method `getMyStock()`, `getWarehouseItems()`, `createRequisition()`, `transferCustody()`, dan `returnMaterial()`.
3. **Antarmuka Pengguna (UI) Inventory**:
   - [`item_custody_material.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/item_custody_material.xml): kartu material custody yang menampilkan kategori, SKU, nama barang, badge serial number, saldo tersedia, rincian (*total, terpakai di SPK, retur*), serta tombol aksi cepat.
   - [`bottom_sheet_inventory.xml`](file:///D:/dsbilling-mobile/app/src/main/res/layout/bottom_sheet_inventory.xml): panel inventori interaktif dengan bar ringkasan (*Total Jenis Barang, Total Unit/Meter*), empty state, dan tombol cepat *+ Minta Barang*.
   - [`CustodyAdapter.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/adapter/CustodyAdapter.kt): adapter RecyclerView material custody.
   - [`InventoryBottomSheet.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/dialog/InventoryBottomSheet.kt):
     - Dialog detail material lengkap.
     - Dialog **Serah Terima Material**: teknisi memilih rekanan teknisi cabang, mengisi kuantitas dan catatan, lalu mentransfer custody secara instan.
     - Dialog **Retur Gudang**: teknisi memilih kondisi barang (*Bagus / Rusak / QC*) dan mencatat alasan kerusakan.
     - Dialog **Permintaan Material Baru**: memilih barang dari katalog gudang dan mengajukan permohonan ke admin gudang.
   - Terintegrasi di menu utama [`MainActivity.kt`](file:///D:/dsbilling-mobile/app/src/main/java/com/genieacs/client/ui/MainActivity.kt) via menu `"📦 Stok & Material (Inventory)"`.

---

## 2. Hasil Pengujian Otomatis

### A. Pengujian Modul Inventory PHPUnit (`tests/Feature/MobileInventoryTest.php`)
```bash
   PASS  Tests\Feature\MobileInventoryTest
  ✓ technician can view my stock custody
  ✓ technician can list warehouse items catalog
  ✓ technician can create material requisition
  ✓ manager can approve requisition and grant custody
  ✓ technician cannot approve requisitions (HTTP 403)
  ✓ technician can transfer custody to another technician
  ✓ technician cannot transfer custody owned by someone else (HTTP 403)
  ✓ technician can return material to warehouse

  Tests:    8 passed (25 assertions)
  Duration: 6.55s
```

### B. Pengujian Regresi Keseluruhan Mobile API
```bash
   PASS  Tests\Feature\MobileWorkforceTest (8 tests, 22 assertions)
   PASS  Tests\Feature\MobileApiAuthTest (8 tests, 18 assertions)
   PASS  Tests\Feature\MobileInventoryTest (8 tests, 25 assertions)

  Total:    24 passed (65 assertions)
```

### C. Hasil Kompilasi APK Android Debug
- Perintah: `.\gradlew assembleDebug --no-daemon`
- Hasil: **`BUILD SUCCESSFUL in 39s`**
- File Output: [`app-debug.apk`](file:///D:/dsbilling-mobile/app/build/outputs/apk/debug/app-debug.apk) (Ukuran: **8.26 MB**).

---

## 3. Matriks Kesiapan Subfase Selanjutnya

| Subfase | Cakupan Pekerjaan | Status |
|---|---|---|
| **PHASE 2A** | Work Order & Operational Workflow (Daftar, Detail, Penugasan, Transisi Status, Approval) | **SELESAI (VERIFIED)** |
| **PHASE 2B** | Inventory & Material Management (Stok Teknisi, Requisition, Serah Terima, Retur) | **SELESAI (VERIFIED)** |
| **PHASE 2C** | Field Service & Work Evidence (Checklist Dinamis, Foto Ber-watermark, Redaman dBm, Tanda Tangan Pelanggan) | **SIAP DIEKSEKUSI** |
| **PHASE 2D** | Device & Network Operations (Scan Barcode/QR ONT, Pairing SN ke SPK, Diagnostik Redaman OPM) | Antrean |
| **PHASE 2E** | Notification, Offline & Synchronization (FCM Notification, Room SQLite Offline Queue, Sync Worker) | Antrean |
| **PHASE 2F** | Reporting & Operational History (Rekap Produktivitas Teknisi, Pemakaian Material Bulanan) | Antrean |
