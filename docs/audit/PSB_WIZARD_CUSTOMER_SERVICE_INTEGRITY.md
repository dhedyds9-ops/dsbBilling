# AUDIT & IMPLEMENTASI: PSB WIZARD CUSTOMER SERVICE & PACKAGE INTEGRITY
## dsBilling Enterprise — Critical PSB Data Integrity Verification Report

> **Dokumen ID:** `AUDIT-PSB-INTEGRITY-20261125`  
> **Status:** **RESOLVED & VERIFIED (100% PASS)**  
> **Otoritas Arsitektur:** [`docs/SSOT.md`](file:///D:/dsBilling/docs/SSOT.md)  
> **Workspace:** `D:\dsBilling` (Laravel 12, Livewire 3/4, SQLite Multi-Tenant)  
> **Tanggal Audit:** 25 November 2026  

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Pada modul instalasi teknisi (PSB Wizard baik Web maupun Mobile), ditemukan laporan kritis di mana teknisi berhasil menyelesaikan alur aktivasi pelanggan, record `Customer` terbentuk pada database dan muncul di daftar pelanggan, namun **data layanan (`CustomerService`), paket langganan (`ServiceProfile`), instans provisioning (`ServiceInstance`), dan akun jaringan (`PPPoEUser`/`HotspotUser`) tidak terhubung atau kosong ("Orphaned Customer")**.

Hal ini menyebabkan:
1. Pelanggan muncul tanpa paket di CRM.
2. Tidak ada penagihan atau invoice recurring yang aktif.
3. ONT tidak dapat dimonitoring atau disinkronkan ke TR-069 GenieACS.
4. SPK/Tiket tertutup dengan status selesai palsu (*false completed*).

Audit mendalam berhasil mengidentifikasi 5 akar masalah utama dan melakukan restrukturisasi arsitektur secara menyeluruh tanpa merusak sistem yang sudah berjalan (*brownfield preservation*).

---

## 2. Temuan & Akar Masalah (Root Causes)

### A. Unbound Domain Repository Interfaces
* **Gejala:** Service container gagal menyelesaikan dependensi interface `ServiceInstanceRepositoryInterface` dan `ProvisionPipelineRepositoryInterface`.
* **Akar Masalah:** Interface domain aggregate didefinisikan pada namespace `Src\Domain\Provisioning\`, tetapi `AppServiceProvider` tidak memiliki binding implementasi ke Eloquent Repository di `App\Repositories\Provisioning\`. Akibatnya, alur pembuatan `ServiceInstance` dan `ProvisionPipeline` gagal saat diinjeksi atau runtime exception.

### B. Transaksi Database Terikat Langsung dengan Hardware Provisioning
* **Gejala:** Ketika koneksi MikroTik mengalami timeout, socket closed, atau router unreachable, seluruh transaksi pelanggan dibatalkan (rollback).
* **Akar Masalah:** Pemanggilan `$this->pppoeService->activatePPPoEUser(...)` diletakkan di dalam `DB::transaction(...)` bersama pembuatan `Customer`, `Contract`, dan `CustomerService`. Jika pelanggan sudah ada di Step 1 (atau sudah commit parsial sebelum wizard), rollback membatalkan pembuatan `CustomerService` dan `ServiceInstance`, menyisakan `Customer` tanpa layanan.

### C. Nullifikasi Paket Akibat Branch & Reseller Global Scopes
* **Gejala:** Paket langganan terisi `null` pada `CustomerService.service_profile_id`.
* **Akar Masalah:** Model `ServiceProfile` menggunakan trait `HasBranchScope` dan `HasResellerScope`. Ketika teknisi cabang tertentu menjalankan aktivasi dengan paket global kantor pusat (`branch_id = null`), query `ServiceProfile::find($id)` mengembalikan `null` karena terfilter scope cabang teknisi.

### D. Misalignment Foreign Key & Silent Swallowing pada Mobile API
* **Gejala:** Endpoint mobile `/api/mobile/network/devices/provision-ont` tidak mengaitkan `CustomerService`.
* **Akar Masalah:** Pada controller mobile, kode lama menetapkan `CustomerService.service_id = $validated['service_profile_id']`. Hal ini melanggar foreign key SQLite karena `service_id` harus merujuk ke tabel katalog `services`, bukan `service_profiles`. Error ini tertangkap oleh blok `catch` kosong yang mendefinisikan "CustomerService deferred", sementara SPK tetap ditandai `completed`.

### E. Relasi Eloquent Belum Tersedia pada Model CustomerService
* **Gejala:** Pemanggilan `$customerService->serviceInstance` menghasilkan `null` atau undefined method.
* **Akar Masalah:** Model `CustomerService` belum memiliki relasi `serviceInstance()` (`hasOne`) dan `serviceInstances()` (`hasMany`) ke model `ServiceInstance`.

---

## 3. Perubahan Arsitektur & Implementasi Solusi

### 3.1. Binding Container & Implementasi Repository Domain
Implementasi interface domain dilakukan pada `ServiceInstanceRepository` dan `ProvisionPipelineRepository` dengan pemetaan Aggregate Root Domain dan metode Eloquent:
- **File:** [`app/Repositories/Provisioning/ServiceInstanceRepository.php`](file:///D:/dsBilling/app/Repositories/Provisioning/ServiceInstanceRepository.php)
- **File:** [`app/Repositories/Provisioning/ProvisionPipelineRepository.php`](file:///D:/dsBilling/app/Repositories/Provisioning/ProvisionPipelineRepository.php)
- **File:** [`app/Providers/AppServiceProvider.php`](file:///D:/dsBilling/app/Providers/AppServiceProvider.php)
```php
$this->app->bind(
    \Src\Domain\Provisioning\ServiceInstanceRepositoryInterface::class,
    \App\Repositories\Provisioning\ServiceInstanceRepository::class
);
$this->app->bind(
    \Src\Domain\Provisioning\ProvisionPipelineRepositoryInterface::class,
    \App\Repositories\Provisioning\ProvisionPipelineRepository::class
);
```

### 3.2. Pemisahan Transaksi Database Inti dari Hardware Provisioning
Restrukturisasi transaksi pada [`ProvisioningService.php`](file:///D:/dsBilling/app/Services/Provisioning/ProvisioningService.php):
1. **Fase 1 (Atomic Core Database Transaction):**
   - Pembuatan/pencarian `Customer` & `Contract`.
   - Resolusi `ServiceProfile` menggunakan `withoutGlobalScopes()`.
   - Pembuatan/pencarian katalog `Service`.
   - Pembuatan `CustomerService` dengan keterikatan `service_profile_id`, `onu_id`, `branch_id`, dan `reseller_id`.
   - Pembuatan `Subscription` dan `Invoice` pertama.
   - Pembuatan `PPPoEUser` atau `HotspotUser`.
   - Pembuatan `ServiceInstance` dan `ProvisionPipeline`.
   - Semua entitas inti di-commit ke database secara permanen.
2. **Fase 2 (Decoupled Hardware Provisioning):**
   - Eksekusi koneksi router MikroTik dijalankan di luar transaksi inti.
   - **Jika Sukses:** Update status `CustomerService.status = 'active'`, `PPPoEUser.status = 'active'`, `ServiceInstance.status = 'active'`.
   - **Jika Gagal:** Exception ditangkap, log dicatat, update status `CustomerService.status = 'provisioning_failed'`, `PPPoEUser.status = 'failed_provisioning'`, `ServiceInstance.status = 'failed'`.
   - **Garansi Integritas:** Tidak ada data pelanggan atau layanan yang hilang akibat kegagalan router hardware.

### 3.3. Penyelarasan Relasi Model & Auto-Cascading Scopes
1. **Model [`CustomerService.php`](file:///D:/dsBilling/app/Models/Customer/CustomerService.php):**
   - Menambahkan relasi `serviceInstance()` (`hasOne`) dan `serviceInstances()` (`hasMany`).
   - Menambahkan auto-inheritance `branch_id` dari `Customer` saat `creating`.
2. **Model [`Customer.php`](file:///D:/dsBilling/app/Models/CRM/Customer.php):**
   - Menambahkan event hook `saved` untuk mengalirkan update `branch_id` ke seluruh `customer_services`, `pppoe_users`, dan `hotspot_users` miliknya.
3. **Model [`PPPoEUser.php`](file:///D:/dsBilling/app/Models/ISP/PPPoEUser.php) & [`HotspotUser.php`](file:///D:/dsBilling/app/Models/ISP/HotspotUser.php):**
   - Menambahkan pewarisan otomatis `branch_id` dan `reseller_id` dari relasi layanan.

### 3.4. Guard Validasi Penyelesaian SPK (Phase E)
Pada [`Wizard.php`](file:///D:/dsBilling/app/Livewire/Isp/Technician/Installation/Wizard.php) dan [`MobileNetworkApiController.php`](file:///D:/dsBilling/app/Http/Controllers/Api/Mobile/MobileNetworkApiController.php):
- SPK (`Installation` atau `WorkOrder`) dan `Ticket` **DILARANG** ditandai `completed` / `resolved` jika entitas inti belum lengkap atau hardware provisioning gagal.
- Jika hardware gagal, SPK tetap berada pada status `in_progress` dengan catatan diagnostik bagi teknisi untuk melakukan retry.

### 3.5. Idempotensi Mutasi Stok & Aktivasi Ulang (Phase F)
- Penggunaan `transaction_id` unik (`PSB-WIZARD-TKT...` / `PROV-...`) dikirimkan ke `StockMutationService::recordPsbConsumption`.
- Jika teknisi menekan tombol submit dua kali atau mengulang proses, sistem mengenali ID transaksi dan tidak melakukan pemotongan stok berulang.
- Query pencarian entitas menggunakan `updateOrCreate` dan pemeriksaan record existing mencegah duplikasi invoice, langganan, atau customer service.

### 3.6. Mobile API Parity & Route Alias
- Memperbaiki controller mobile untuk mencari katalog ID dari tabel `services` secara tepat.
- Menambahkan route alias `Route::post('/onu/provision', ...)` di samping `/devices/provision-ont` pada [`routes/api.php`](file:///D:/dsBilling/routes/api.php).

---

## 4. Tabel Verifikasi 12 Kriteria Pengujian (Phase G)

Seluruh 12 kriteria diuji secara otomatis melalui test suite [`tests/Feature/PsbWizardCustomerServiceIntegrityTest.php`](file:///D:/dsBilling/tests/Feature/PsbWizardCustomerServiceIntegrityTest.php):

| No | Kriteria Pengujian | Test Method | Status | Hasil Assertions |
|:---|:---|:---|:---:|:---|
| **1** | Registrasi wizard menghasilkan seluruh entitas inti (`Customer`, `Contract`, `CustomerService`, `ServiceInstance`, `ServiceProfile`, `PPPoEUser`, `ONU`). | `test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope` | **PASS** | Semua entitas terverifikasi terbentuk lengkap di DB. |
| **2** | Relasi `CustomerService -> ServiceProfile` terisi dan tidak null. | `test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope` | **PASS** | `assertNotNull($cs->serviceProfile)` & ID paket cocok. |
| **3** | Relasi `CustomerService -> ServiceInstance` terisi dan dapat diakses. | `test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope` | **PASS** | `assertNotNull($cs->serviceInstance)` & instance terikat. |
| **4** | Relasi `ServiceInstance -> ProvisionPipeline` terisi. | `test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope` | **PASS** | `assertNotNull($instance->provisionPipeline)` valid. |
| **5** | `ServiceInstanceRepositoryInterface` dapat di-resolve via container. | `test_service_container_resolves_provisioning_repository_interfaces` | **PASS** | `app(ServiceInstanceRepositoryInterface::class)` sukses. |
| **6** | `ProvisionPipelineRepositoryInterface` dapat di-resolve via container. | `test_service_container_resolves_provisioning_repository_interfaces` | **PASS** | `app(ProvisionPipelineRepositoryInterface::class)` sukses. |
| **7** | Kegagalan MikroTik/OLT tidak menyebabkan rollback Customer & Layanan. | `test_hardware_failure_does_not_rollback_core_data_and_reflects_actual_status` | **PASS** | Customer & Service tetap ada di DB saat koneksi gagal. |
| **8** | Status layanan mencerminkan hasil aktual (bukan active palsu saat gagal). | `test_hardware_failure_does_not_rollback_core_data_and_reflects_actual_status` | **PASS** | Status `provisioning_failed`, PPPoE `failed_provisioning`. |
| **9** | SPK tidak dapat diselesaikan jika layanan/provisioning gagal. | `test_spk_and_ticket_remain_in_progress_when_hardware_fails_and_complete_on_success` | **PASS** | SPK tetap `in_progress` saat gagal, `completed` saat sukses. |
| **10** | Retry wizard aman dari duplikasi record atau error integritas (Idempotent). | `test_wizard_idempotency_and_retry_safety` | **PASS** | Count record tepat 1 untuk Customer, CS, PPPoE, Invoice. |
| **11** | Branch ID dan Reseller ID terisi konsisten pada seluruh entitas terkait. | `test_provisioning_service_creates_all_core_entities_and_relations_with_branch_and_reseller_scope` | **PASS** | Branch & Reseller cocok di Customer, CS, PPPoE, ONU. |
| **12** | Endpoint mobile `/api/mobile/network/onu/provision` menghasilkan relasi CS dan ServiceInstance lengkap. | `test_mobile_onu_provision_endpoint_creates_customer_service_and_service_instance` | **PASS** | Response HTTP 200, CS terbentuk, Instance terbentuk, SPK selesai. |

---

## 5. Bukti Eksekusi Test Suite (Test Log)

### A. Eksekusi Test Integrity PSB (7 Tests, 77 Assertions)
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.28
Configuration: D:\dsBilling\phpunit.xml

.......                                                             7 / 7 (100%)

Time: 00:27.466, Memory: 92.00 MB

OK (7 tests, 77 assertions)
```

### B. Eksekusi Seluruh Modul PSB Regression (16 Tests, 100 Assertions)
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.28
Configuration: D:\dsBilling\phpunit.xml

................                                                  16 / 16 (100%)

Time: 00:28.419, Memory: 104.00 MB

OK (16 tests, 100 assertions)
```

### C. Eksekusi Mobile Endpoint Hotfix Tests (12 Tests, 59 Assertions)
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.28
Configuration: D:\dsBilling\phpunit.xml

............                                                      12 / 12 (100%)

Time: 00:04.986, Memory: 88.00 MB

OK (12 tests, 59 assertions)
```

---

## 6. Panduan Developer & Operasional Lapangan

### Untuk Software Engineer:
1. **Jangan Sekali-kali Membungkus Driver Hardware dalam Transaksi Inti DB:**  
   Pemisahan transaksi database inti (Customer, Contract, CustomerService, Paket, Invoice) dari interaksi soket network hardware (MikroTik RouterOS, OLT CLI, GenieACS TR-069) adalah aturan mutlak SSOT.
2. **Gunakan Scope Bypassing Secara Sadar:**  
   Saat mereferensikan paket katalog global, selalu gunakan `ServiceProfile::withoutGlobalScopes()->find($id)` agar paket global HQ tidak dianggap `null` oleh query yang terikat cabang teknisi.
3. **Wajib Membawa Transaksi ID ke Stock Mutation:**  
   Setiap alur pemotongan material lapangan wajib menyertakan parameter `transactionId` deterministik untuk mencegah pemotongan ganda saat retry.

### Untuk Teknisi Lapangan:
1. **Status SPK "In Progress" pada Notifikasi Gagal Router:**  
   Jika layar wizard menampilkan pesan router gagal dijangkau, data pelanggan sudah aman tersimpan di sistem pusat. Teknisi dapat memeriksa koneksi IP/VLAN router dan menekan tombol Retry tanpa khawatir membuat data ganda atau memotong barang dua kali.
2. **Kewajiban Pengambilan Barang ke Gudang (Custody):**  
   ONT tidak dapat diaktivasi jika serial number tidak terdaftar dalam custody aktif teknisi yang bersangkutan.

---
*Laporan ini disusun secara otomatis dan diverifikasi melalui test harness Google Antigravity IDE dsBilling.*
