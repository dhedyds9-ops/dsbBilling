# PHASE 1C — CUSTOMER BILLING DUE DATE MANAGEMENT AUDIT REPORT
## dsBilling Enterprise — PPPoE & Hotspot Billing Lifecycle

> **Author:** Antigravity Autonomous Pair Programming  
> **Date:** 2026-11-26  
> **Target System:** dsBilling Backend & Enterprise Admin Portal (Laravel 12 / Livewire 3/4)  
> **Status:** COMPLETED & VERIFIED (12/12 Automated Feature Tests Passing)

---

## 1. Executive Summary

Fitur **Customer Billing Due Date Management** pada Phase 1C telah diimplementasikan secara menyeluruh pada modul PPPoE dan Hotspot. Pengaturan tanggal jatuh tempo kini tersedia di seluruh siklus hidup layanan:
1. **Pembuatan Layanan Baru (Create):** Input tanggal jatuh tempo pertama pada Create PPPoE dan Create Hotspot.
2. **Pembaruan Layanan (Edit):** Form input tanggal jatuh tempo baru dengan validasi dan pencatatan alasan perubahan pada Edit PPPoE dan Edit Hotspot.
3. **Detail Layanan (Show):** Kartu ringkasan billing serta Modal Interaktif "Edit Jatuh Tempo" yang memungkinkan operator/manager memperbarui tanggal jatuh tempo langsung dari halaman detail tanpa mengganggu konfigurasi teknis atau MikroTik.

Sistem mematuhi prinsip Single Source of Truth (SSOT), isolasi multi-tenant (branch & reseller), proteksi invoice yang sudah lunas (immutability), serta audit trail lengkap di `audit_logs`.

---

## 2. Sumber SSOT Tanggal Jatuh Tempo

Berdasarkan audit arsitektur database dsBilling, tanggal jatuh tempo dikelola pada level entitas billing dan layanan (bukan sekadar atribut sementara di akun router):

| Level Entitas | Tabel / Kolom | Peran SSOT & Perilaku |
| :--- | :--- | :--- |
| **Service Entity (SSOT Layanan)** | `customer_services.attributes->due_date` | Menyimpan tanggal jatuh tempo spesifik untuk instansi layanan (`CustomerService`). Mendukung pelanggan dengan banyak layanan berbeda. |
| **Billing Schedule (SSOT Siklus)** | `subscriptions.next_billing_date` | Menentukan kapan cron billing akan menagihkan paket dan menerbitkan invoice berikutnya untuk layanan tersebut. |
| **Invoice Entity (SSOT Finansial)** | `invoices.due_date` | Tanggal batas akhir pembayaran untuk faktur tagihan tertentu. |
| **Proxy Accessor (Convenience)** | `PPPoEUser->due_date`<br>`HotspotUser->due_date` | Eloquent accessor yang mendelegasikan query ke `CustomerBillingDueDateService::getDueDate()`. Tidak menduplikasi kolom di tabel fisik router. |

---

## 3. Aturan & Kebijakan Perubahan Invoice

Saat tanggal jatuh tempo diubah melalui `CustomerBillingDueDateService::updateDueDate()`:

1. **Invoice Terbayar (Paid / Lunas) Bersifat Immutable:**
   - Seluruh invoice dengan `status = 'paid'` **TIDAK PERNAH DIUBAH** dalam kondisi apa pun untuk menjaga kepatuhan rekonsiliasi akuntansi dan integritas pembukuan finansial.
2. **Invoice Belum Terbayar (Unpaid / Pending):**
   - Invoice aktif yang belum dibayar (`status IN ('unpaid', 'pending')`) yang terasosiasi dengan layanan yang diubah akan diperbarui kolom `due_date`-nya mengikuti tanggal jatuh tempo yang baru.
3. **Pencegahan Duplikasi Invoice:**
   - Perubahan tanggal jatuh tempo **TIDAK MENERBITKAN** invoice baru. Sistem hanya memperbarui tanggal jatuh tempo pada invoice yang ada serta menyesuaikan `subscriptions.next_billing_date`.
4. **Isolasi Layanan (Multi-Service Support):**
   - Jika satu pelanggan memiliki beberapa layanan (misal: 1 PPPoE rumah + 1 Hotspot kantor), perubahan jatuh tempo pada satu layanan **hanya berdampak pada layanan tersebut**. Layanan lain dan invoice layanan lain tetap tidak terpengaruh.
5. **Independensi Konfigurasi Perangkat (MikroTik):**
   - Perubahan jatuh tempo tidak pernah mengubah username, password, profile bandwidth, status secret, atau parameter MikroTik lainnya.
6. **Audit Trail:**
   - Setiap mutasi dicatat di tabel `audit_logs` dengan informasi: `auditable_type`, `auditable_id`, `old_values` (due date lama), `new_values` (due date baru), `user_id` (operator pemohon), timestamp, dan catatan alasan perubahan (`notes`).

---

## 4. Otorisasi & Scoping (RBAC & Tenant Isolation)

Sesuai aturan keamanan arsitektur dsBilling:
- **Role & Permission:** Hanya role dengan permission billing (`invoice.update` / `customer.update`) atau pengguna dengan role level `Manager` / `Kasir` / `Administrator` yang berhak mengubah jatuh tempo.
- **Teknisi & NOC Guard:** Role `Teknisi` dan `NOC` secara eksplisit ditolak melakukan perubahan jatuh tempo kecuali memiliki izin billing khusus (`invoice.update`).
- **Branch Scope:** Petugas cabang hanya diizinkan mengelola pelanggan di cabangnya sendiri (`user->branch_id === customer_service->branch_id`).
- **Reseller Scope:** Mitra reseller hanya diizinkan melihat dan mengelola jatuh tempo pelanggan miliknya sendiri (`user->reseller_id === customer_service->reseller_id`). Percobaan mutasi lintas tenant (cross-tenant attack) akan melempar `Illuminate\Auth\Access\AuthorizationException`.

---

## 5. File yang Diubah & Dibuat

### A. Domain Service & Backend Models
1. **[`app/Services/Billing/CustomerBillingDueDateService.php`](file:///d:/dsBilling/app/Services/Billing/CustomerBillingDueDateService.php)** (Baru):
   - Service pusat penanganan pembacaan due date (`getDueDate`), validasi hak akses & isolasi tenant (`canManageDueDate`), mutasi jatuh tempo transaksi (`updateDueDate`), serta pencatatan audit log.
2. **[`app/Models/Customer/CustomerService.php`](file:///d:/dsBilling/app/Models/Customer/CustomerService.php)**:
   - Ditambahkan relasi `subscription()` dan accessor `getDueDateAttribute()`.
3. **[`app/Models/ISP/PPPoEUser.php`](file:///d:/dsBilling/app/Models/ISP/PPPoEUser.php)**:
   - Ditambahkan accessor `getDueDateAttribute()`.
4. **[`app/Models/ISP/HotspotUser.php`](file:///d:/dsBilling/app/Models/ISP/HotspotUser.php)**:
   - Ditambahkan accessor `getDueDateAttribute()`.
5. **[`app/Services/Provisioning/ProvisioningService.php`](file:///d:/dsBilling/app/Services/Provisioning/ProvisioningService.php)**:
   - Pada `activatePPPoEService` dan `activateHotspotService`: menerima input `due_date`, menyimpan ke `CustomerService->attributes['due_date']`, menyetel `Subscription->next_billing_date`, dan menyelaraskan `due_date` invoice pertama jika invoice dibuat via recurring job listener.

### B. Livewire Components & Blade Views
1. **PPPoE Create:**
   - [`app/Livewire/Isp/PPPoEUser/Create.php`](file:///d:/dsBilling/app/Livewire/Isp/PPPoEUser/Create.php): deklarasi `$due_date`, validasi date, pengiriman payload aktivasi.
   - [`resources/views/livewire/isp/pppoe-user/create.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/pppoe-user/create.blade.php): input field Tanggal Jatuh Tempo.
2. **PPPoE Edit:**
   - [`app/Livewire/Isp/PPPoEUser/Edit.php`](file:///d:/dsBilling/app/Livewire/Isp/PPPoEUser/Edit.php): form input `$due_date` & `$due_date_reason`, sinkronisasi via `CustomerBillingDueDateService`.
   - [`resources/views/livewire/isp/pppoe-user/edit.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/pppoe-user/edit.blade.php): form input Jatuh Tempo dan Alasan Perubahan.
3. **PPPoE Show (Detail):**
   - [`app/Livewire/Isp/PPPoEUser/Show.php`](file:///d:/dsBilling/app/Livewire/Isp/PPPoEUser/Show.php): method `openEditDueDateModal()`, `closeEditDueDateModal()`, `saveDueDate()`.
   - [`resources/views/livewire/isp/pppoe-user/show.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/pppoe-user/show.blade.php): kartu "Informasi Billing & Jatuh Tempo", tombol "Edit Jatuh Tempo", dan modal interaktif.
4. **Hotspot Create:**
   - [`app/Livewire/Isp/HotspotUser/Create.php`](file:///d:/dsBilling/app/Livewire/Isp/HotspotUser/Create.php): deklarasi `$due_date`, validasi date, pengiriman payload aktivasi.
   - [`resources/views/livewire/isp/hotspot-user/create.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/hotspot-user/create.blade.php): input field Tanggal Jatuh Tempo.
5. **Hotspot Edit:**
   - [`app/Livewire/Isp/HotspotUser/Edit.php`](file:///d:/dsBilling/app/Livewire/Isp/HotspotUser/Edit.php): form input `$due_date` & `$due_date_reason`, sinkronisasi via `CustomerBillingDueDateService`.
   - [`resources/views/livewire/isp/hotspot-user/edit.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/hotspot-user/edit.blade.php): form input Jatuh Tempo dan Alasan Perubahan.
6. **Hotspot Show (Detail):**
   - [`app/Livewire/Isp/HotspotUser/Show.php`](file:///d:/dsBilling/app/Livewire/Isp/HotspotUser/Show.php): method `openEditDueDateModal()`, `closeEditDueDateModal()`, `saveDueDate()`.
   - [`resources/views/livewire/isp/hotspot-user/show.blade.php`](file:///d:/dsBilling/resources/views/livewire/isp/hotspot-user/show.blade.php): kartu "Informasi Billing & Jatuh Tempo", tombol "Edit Jatuh Tempo", dan modal interaktif.

### C. Testing Suite
- **[`tests/Feature/CustomerBillingDueDateTest.php`](file:///d:/dsBilling/tests/Feature/CustomerBillingDueDateTest.php)**: 12 skenario pengujian komprehensif.

---

## 6. Hasil Pengujian Otomatis (Test Verification)

Pengujian dijalankan melalui PHPUnit 11.5.55:

```bash
.\vendor\bin\phpunit.bat tests/Feature/CustomerBillingDueDateTest.php
```

### Hasil Test:
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.28
Configuration: D:\dsBilling\phpunit.xml

............                                                      12 / 12 (100%)

Time: 00:08.689, Memory: 92.00 MB

OK (12 tests, 51 assertions)
```

| No | Nama Skenario Test | Status | Keterangan Verifikasi |
| :---: | :--- | :---: | :--- |
| 1 | `test_01_create_pppoe_with_due_date` | **PASSED** | Due date tersimpan di `CustomerService`, `Subscription`, dan `Invoice` pertama. |
| 2 | `test_02_edit_pppoe_due_date` | **PASSED** | Perubahan due date via Edit PPPoE berhasil mengupdate entitas dan audit log. |
| 3 | `test_03_edit_pppoe_due_date_from_detail` | **PASSED** | Modal di Detail PPPoE (`PPPoEUser/Show`) berhasil mengubah due date. |
| 4 | `test_04_create_hotspot_with_due_date` | **PASSED** | Due date tersimpan di `CustomerService`, `Subscription`, dan `Invoice` Hotspot. |
| 5 | `test_05_edit_hotspot_due_date` | **PASSED** | Perubahan due date via Edit Hotspot berhasil mengupdate entitas dan audit log. |
| 6 | `test_06_edit_hotspot_due_date_from_detail` | **PASSED** | Modal di Detail Hotspot (`HotspotUser/Show`) berhasil mengubah due date. |
| 7 | `test_07_customer_with_multiple_services_has_independent_due_dates` | **PASSED** | Pelanggan dengan PPPoE dan Hotspot memiliki due date independen; mutasi pada satu layanan tidak menyentuh layanan lainnya. |
| 8 | `test_08_paid_invoices_are_not_modified_when_due_date_changes` | **PASSED** | Invoice dengan status `paid` terbukti immutable (tanggal jatuh tempo tidak bergeser). |
| 9 | `test_09_unpaid_invoices_update_due_date` | **PASSED** | Seluruh invoice berstatus `unpaid` otomatis diperbarui mengikuti tanggal baru. |
| 10 | `test_10_user_without_billing_permission_is_rejected` | **PASSED** | User tanpa hak akses billing (seperti teknisi) melempar `AuthorizationException`. |
| 11 | `test_11_cross_tenant_update_is_rejected` | **PASSED** | Reseller/cabang lain yang mencoba mengubah due date pelanggan di luar cakupannya ditolak tegas. |
| 12 | `test_12_no_duplicate_invoices_generated_after_due_date_change` | **PASSED** | 3x pergantian due date berturut-turut terbukti tidak melipatgandakan jumlah invoice (tetap 1 invoice). |

### Regression Check:
- `tests/Feature/PsbWizardCustomerServiceIntegrityTest.php`: **14 / 14 (100%) PASSED**
- `tests/Feature/TicketNotificationDuplicateTest.php`: **1 / 1 (100%) PASSED**

---

## 7. Risiko Migrasi & Kompatibilitas Data Lama

1. **Kompatibilitas Pelanggan Lama (Brownfield Compatibility):**
   - Pelanggan lama yang belum memiliki atribut `due_date` di `customer_services.attributes` tetap berfungsi normal. Accessor `getDueDateAttribute()` secara otomatis melakukan fallback bertahap ke `subscriptions.next_billing_date`, lalu ke `invoices.due_date` aktif terakhir, atau default ke null/hari ini.
2. **Zero Schema Alteration Risk:**
   - Tidak ada `ALTER TABLE`, drop table, atau penambahan kolom fisik baru pada tabel router (`pppoe_users`, `hotspot_users`). Seluruh data disimpan terpusat di `customer_services.attributes` (JSON) dan tabel billing standar Laravel dsBilling (`subscriptions` dan `invoices`).
3. **Hardware Isolation:**
   - Interaksi dengan MikroTik (RouterOS) terisolasi penuh; rotasi tanggal jatuh tempo murni beroperasi di layer bisnis backend tanpa memicu pemutusan sesi PPPoE/Hotspot pelanggan yang sedang online.
