# MATRIKS PARITAS FITUR & BUKTI INTEGRASI: WEB DSBILLING VS ANDROID STAFF

> **Otoritas Verifikasi:** Senior QA Architect, Android Engineer, Laravel Backend Auditor  
> **Target Verifikasi:**  
> - Backend & Web Portal: `D:\dsBilling` (Laravel 12, Livewire 3/4, Tailwind CSS, SQLite Multi-Tenant)  
> - Android Native Staff: `D:\dsbilling-mobile` (Kotlin, Material Design 3, OkHttp/Coroutines)  
> **Standar Status Paritas:**  
> - `MATCH`: Seluruh alur proses bisnis telah terbukti sama (UI, API, Controller, Service, Database).  
> - `PARTIAL`: Fitur tersedia namun ada perbedaan alur atau fitur platform-specific tanpa padanan langsung.  
> - `MISSING`: Fitur ada di Web tetapi belum diimplementasikan di Android.  
> - `BROKEN`: Fitur tersedia tetapi mengalami kegagalan fungsi atau error saat dieksekusi.  
> - `NOT APPLICABLE`: Fitur dikhususkan untuk platform Web Desktop (misal ekspor cetak Excel admin).  
> - `NOT VERIFIED`: Kode controller & service telah terintegrasi, namun belum diuji secara fisik dengan perangkat keras menyala di lapangan.  

---

## 1. TABEL EVALUASI PARITAS FITUR UTAMA

| ID | Modul / Fitur | Web Portal Teknisi (`D:\dsBilling`) | Android Staff Native (`D:\dsbilling-mobile`) | Status Paritas | Bukti Alur & Analisis Teknis |
| :---: | :--- | :--- | :--- | :---: | :--- |
| **D01** | **Dashboard KPI** | 4 Metrik Ringkasan: SPK Aktif, SPK Selesai, Modem ONT, Stok Tangan | KPI Card 4 metrik sinkron live dari backend API | **MATCH** | Android `MainActivity.kt` memanggil `api.getWorkOrders()` & `api.getMyStock()`. Data live, bukan hardcoded. |
| **D02** | **Shift Status Absensi** | Banner sticky "Belum Absen Masuk" + lock state | Card Shift Absensi ("Sudah Absen" vs "Belum") | **MATCH** | Android `MainActivity.kt` memanggil `api.getTodayAttendance()`. Mengarahkan langsung ke `AttendanceActivity`. |
| **D03** | **Aksi Cepat (Quick Actions)** | 8 menu operasional lapangan | Grid 8 tombol Material 3 navigasi full-screen | **MATCH** | Seluruh 8 tombol membuka Activity Full-Screen dedicated (`WorkOrder`, `ProvisionOnt`, `SwapOnt`, `Dismantle`, `Diagnostic`, `Inventory`, `NetworkMap`, `Payroll`). |
| **D04** | **Aktivitas Terbaru & "Lihat Semua"** | Feed log pekerjaan & tombol riwayat | 3 Card aktivitas dinamis + tombol `tvBtnSeeAllActivities` | **MATCH** | Android `MainActivity.kt` memanggil `api.getActivityLogs(limit = 3)` live. Klik membuka `OperationalReportsActivity`. |
| **A01** | **Absen Masuk (Check-In)** | Web form GPS coordinate + tombol Absen | Full-Screen `AttendanceActivity` + GPS Leaflet | **MATCH** | Android UI -> `api.checkInAttendance()` -> `MobileAttendanceApiController::checkIn` -> Multi-branch Haversine Geofencing -> Database `attendances`. |
| **A02** | **Absen Pulang (Check-Out)** | Web form catatan selesai shift | Full-Screen `AttendanceActivity` tombol Check-Out | **MATCH** | Android UI -> `api.checkOutAttendance()` -> `MobileAttendanceApiController::checkOut` -> Update `checked_out_at`. |
| **W01** | **Daftar SPK (Work Orders)** | Tab PSB, Gangguan, Maintenance | Full-Screen `WorkOrderActivity` dengan Tab M3 | **MATCH** | Android UI -> `api.getWorkOrders()` -> `MobileWorkforceApiController::workOrders` -> Eloquent query scoped by `technician_id` & `branch_id`. |
| **W02** | **Pencarian SPK** | Input filter pencarian realtime | Search bar debounce di `WorkOrderActivity` | **MATCH** | Pencarian berdasarkan nomor SPK, nama pelanggan, dan jenis pekerjaan. |
| **W03** | **Detail SPK & Aksi** | Halaman detail pekerjaan & kontak pelanggan | Full-Screen `WorkOrderDetailActivity` | **MATCH** | Menampilkan info kontak, tombol Call/WhatsApp/Peta, checklist, dan penugasan material. |
| **W04** | **Eksekusi Status SPK** | Tombol Mulai Dikerjakan & Selesai | Action buttons di `WorkOrderDetailActivity` | **MATCH** | Android UI -> `api.updateWorkOrderStatus()` -> `MobileWorkforceApiController::updateStatus` -> DB update `work_orders`. |
| **W05** | **Checklist Pekerjaan** | Checkbox tahapan SOP lapangan | Checkbox dinamis di `WorkOrderDetailActivity` | **MATCH** | Android UI -> `api.toggleChecklist()` -> `MobileWorkforceApiController::toggleChecklist` -> Database `work_order_checklists`. |
| **W06** | **Pencatatan Redaman Optik** | Form optical power meter (dBm) | Form input sinyal di `WorkEvidenceBottomSheet` | **MATCH** | Android UI -> `api.saveMeasurements()` -> `MobileWorkforceApiController::saveMeasurements` -> Validasi dBm numerik. |
| **W07** | **Pemakaian Material SPK** | Dropdown material dari stok tangan teknisi | Form material di `WorkEvidenceBottomSheet` | **MATCH** | Android UI -> `api.addMaterialUsage()` -> `MobileWorkforceApiController::addMaterialUsage` -> DB transaction memotong stok tangan. |
| **W08** | **Upload Foto Bukti Kerja** | Unggah dokumentasi foto ODP/ONT | Dialog upload foto dengan kamera / galeri | **MATCH** | Android UI -> `api.uploadEvidencePhoto()` -> `MobileWorkforceApiController::uploadEvidence` -> Multipart storage upload. |
| **W09** | **Tanda Tangan Pelanggan** | HTML Canvas signature pad | Custom Canvas `SignatureView` di Android | **MATCH** | Android Canvas -> PNG Base64 -> `api.saveSignature()` -> `MobileWorkforceApiController::saveSignature` -> Disimpan ke disk backend. |
| **O01** | **Pasang Baru (PSB ONT)** | Wizard registrasi ONT & binding ODP | Full-Screen `ProvisionOntActivity` | **MATCH** | Android UI -> `api.provisionOnt()` -> `MobileNetworkApiController::provisionOnt` -> DB transaction `Onu` + `CustomerService` binding. |
| **O02** | **Ganti ONT (Swap ONT)** | Form migrasi konfigurasi modem rusak | Full-Screen `SwapOntActivity` | **MATCH** | Android UI -> `api.swapOnt()` -> `MobileNetworkApiController::swapOnt` -> DB transaction, migrasi ODP/PPPoE + Branch Isolation Guard. |
| **O03** | **Cabut ONT (Dismantle)** | Form penarikan modem pelanggan berhenti | Full-Screen `DismantleActivity` | **MATCH** | Android UI -> `api.dismantle()` -> `MobileNetworkApiController::dismantle` -> Unbind `CustomerService`, ubah status ONT + Branch Guard. |
| **O04** | **Diagnosa TR-069 (Sinyal/Status)**| Cek redaman, serial number, uptime | Full-Screen `DiagnosticActivity` | **MATCH** | Android UI -> `api.getDeviceDiagnostics()` -> `MobileNetworkApiController::deviceDiagnostics` -> Query TR-069 virtual parameters. |
| **O05** | **Remote Reboot Perangkat ONT** | Tombol Reboot pada portal | Tombol Reboot di `DiagnosticActivity` / `DeviceDetail`| **NOT VERIFIED** | Kode proxy controller & service valid, namun **memerlukan verifikasi fisik dengan perangkat keras menyala di lapangan**. |
| **O06** | **Konfigurasi WiFi (SSID/Password)**| Modal setting WiFi & mode keamanan | Form WiFi di `DeviceDetailActivity` | **NOT VERIFIED** | Proxy controller telah mendukung BeaconType ZTE/Huawei, namun **memerlukan verifikasi fisik dengan perangkat keras menyala**. |
| **O07** | **Konfigurasi WAN (PPPoE/IP)**| Manajemen WAN TR-069 & VLAN | Form WAN di `DeviceDetailActivity` & `AddWanBottomSheet` | **NOT VERIFIED** | Driver WAN Manager backend telah diperbaiki, namun **memerlukan verifikasi fisik dengan perangkat keras menyala**. |
| **I01** | **Stok Tangan Teknisi** | Tabel daftar material dalam penguasaan teknisi| Full-Screen `InventoryActivity` Tab Stok Saya | **MATCH** | Android UI -> `api.getMyStock()` -> `MobileInventoryApiController::myStock` -> Menampilkan dropcore, ONT, connector. |
| **I02** | **Katalog Stok Gudang Cabang** | Katalog barang gudang cabang | Full-Screen `InventoryActivity` Tab Gudang | **MATCH** | Android UI -> `api.getWarehouseItems()` -> `MobileInventoryApiController::warehouseItems`. |
| **I03** | **Permintaan Barang (Requisition)**| Form request material ke admin gudang | Form Permintaan di `InventoryActivity` | **MATCH** | Android UI -> `api.createRequisition()` -> `MobileInventoryApiController::createRequisition` -> DB transaction `inventory_requisitions`. |
| **I04** | **Retur Material Rusak/Bekas** | Form pengembalian barang tarikan | Form Retur di `InventoryActivity` | **MATCH** | Android UI -> `api.returnMaterial()` -> `MobileInventoryApiController::returnMaterial` -> DB `inventory_returns`. |
| **M01** | **Peta Jaringan & Titik ODP** | Peta Leaflet marker ODP & jalur kabel | Full-Screen `NetworkMapActivity` dengan Leaflet | **MATCH** | Android UI -> `api.getOdps()` -> `MobileNetworkApiController::odps` -> Render titik ODP dan kapasitas port pada peta. |
| **P01** | **Riwayat Slip Gaji (Payroll)** | Tabel riwayat penggajian bulanan | Full-Screen `PayrollActivity` | **MATCH** | Android UI -> `api.getPayrollHistory()` -> `MobilePayrollApiController::index` -> Menampilkan gaji pokok, insentif SPK, potongan, THP. |
| **C01** | **Daftar Pelanggan Cabang** | Master direktori pelanggan cabang | Full-Screen `CustomerActivity` | **MATCH** | Android UI -> `api.getCustomers()` -> `MobileCustomerApiController::index` -> Scoped by `branch_id` / `reseller_id`. |
| **C02** | **Pencarian Pelanggan** | Filter nama, nomor internet, HP | Remote debounced backend search di `CustomerActivity`| **MATCH** | Android UI -> Debounce 500ms `?q=...&per_page=50` -> `MobileCustomerApiController::index` -> Query database backend. |
| **V01** | **Asisten Suara AI (Voice Assistant)**| *Tidak tersedia padanan langsung di Web* | `VoiceAssistBottomSheet` dengan Speech-to-Text | **PARTIAL** | Fitur unggulan Android Native. Menggunakan speech recognizer lokal dan backend proxy `/api/mobile/ai/ask` ke Gemini AI. |
| **S01** | **Autentikasi & Keamanan Sesi**| Laravel session cookies | Sanctum Personal Access Token via Android Keystore| **MATCH** | Token dienkripsi hardware TEE (AES-256 GCM). Auto-logout saat 401 SessionExpired. |
| **S02** | **Dukungan Dark Mode & Light Mode**| Tailwind dark class switch | Material 3 DayNight theme (`@color/genie_card_bg`) | **MATCH** | Seluruh tampilan adaptif tanpa white-on-white atau black-on-black bug. |

---

## 2. REKAPITULASI STATUS PARITAS

- **Total Fitur Diaudit:** 33 Fitur
- **MATCH (Terbukti 100% dari UI hingga DB):** 29 Fitur (87.9%)
- **PARTIAL (Spesifik Mobile / Tanpa Padanan Web):** 1 Fitur (3.0% - Asisten Suara AI)
- **NOT VERIFIED (Memerlukan Uji Fisik Perangkat Keras Lapangan):** 3 Fitur (9.1% - Remote Reboot, WiFi Push, WAN Config)
- **MISSING:** 0 Fitur (0%)
- **BROKEN:** 0 Fitur (0%)
