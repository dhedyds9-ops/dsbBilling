# WiFiNan ERP
# Single Source of Truth (SSOT)

Version : 1.0
Status  : Official
Last Update : 2026-07-06

---

# 1. Tujuan

Dokumen ini adalah standar resmi (Single Source of Truth) seluruh pengembangan WiFiNan ERP.

Seluruh developer, AI Assistant, reviewer, dan kontributor wajib mengikuti dokumen ini.

Jika implementasi bertentangan dengan SSOT, maka implementasi harus diperbaiki, bukan SSOT yang diubah.

---

# 2. Visi Proyek

WiFiNan ERP adalah Enterprise Resource Planning (ERP) khusus Internet Service Provider (ISP).

Target akhirnya bukan hanya aplikasi billing, tetapi platform operasional ISP yang lengkap, modern, scalable, dan mudah dikembangkan.

---

# 3. Sasaran

WiFiNan ERP harus mendukung:

- Multi Tenant
- Multi Branch
- Multi Owner
- Multi Router
- Multi Vendor
- Multi Service
- Enterprise Ready
- Cloud Ready
- On-Premise Ready
- API Ready

---

# 4. Modul Utama

Semua pengembangan harus mengarah pada modul berikut.

- Dashboard
- CRM
- ISP
- Billing
- Finance
- Inventory
- HRD
- Reporting
- Monitoring
- Automation
- System

Tidak boleh membuat modul di luar struktur tersebut tanpa persetujuan arsitektur.

# 4A. Scope of Work (Ruang Lingkup Pekerjaan)

Setiap analisis, audit, refactoring, maupun implementasi wajib menentukan ruang lingkup pekerjaan sebelum memulai.

Scope dibagi menjadi tiga level.

### LEVEL 1 — Project

Mencakup seluruh WiFiNan ERP.

Contoh:

- Dashboard
- CRM
- ISP
- Billing
- Finance
- Inventory
- HRD
- Reporting
- Monitoring
- Automation
- System

AI tidak boleh menyatakan proyek telah selesai apabila hanya mengerjakan satu modul.

---

### LEVEL 2 — Module

Mencakup satu modul penuh.

Contoh:

ISP

↓

- Vendor
- Tower
- POP
- ODC
- ODP
- OLT
- ONU
- Router
- IP Pool
- Service Profile
- PPP Profile
- Hotspot
- Radius

AI tidak boleh menyatakan seluruh ERP telah sesuai standar apabila hanya mengerjakan satu modul.

---

### LEVEL 3 — Feature

Mencakup satu fitur di dalam modul.

Contoh:

Service Profile

↓

- Index
- Create
- Edit
- Show
- Archive
- Bulk Action
- Service
- Validation
- Policy

AI tidak boleh menyatakan seluruh modul telah selesai apabila hanya mengerjakan satu feature.

---

Setiap pekerjaan WAJIB menampilkan informasi berikut pada awal laporan.

Scope

LEVEL 1 / LEVEL 2 / LEVEL 3

Coverage

Daftar modul atau feature yang dianalisis.

Status

Planning
In Progress
Completed

AI dilarang memberikan kesimpulan di luar ruang lingkup pekerjaan yang sedang dikerjakan.

# 5. Arsitektur

Seluruh modul wajib mengikuti alur berikut.

Presentation

↓

Livewire

↓

Service

↓

Repository (Opsional)

↓

Model

↓

Database

Aturan:

- Blade hanya untuk tampilan.
- Livewire hanya mengatur state dan interaksi.
- Service adalah pusat business logic.
- Repository digunakan bila query kompleks.
- Model hanya mengelola relasi dan data.
- Database menjadi sumber data utama.

Business Logic dilarang berada di:

- Blade
- Controller
- Livewire View

---

# 6. Prinsip Pengembangan

Seluruh fitur wajib mengikuti prinsip berikut.

- Konsisten
- Reusable
- Modular
- Scalable
- Maintainable
- Enterprise Ready

Lebih baik menggunakan pola yang sama daripada membuat pola baru.

---

# 7. Prinsip UI

Semua halaman harus memiliki pengalaman pengguna yang sama.

Tidak boleh ada:

- Layout berbeda
- Tombol berbeda
- Form berbeda
- Toolbar berbeda
- Warna berbeda

Seluruh standar UI dijelaskan pada:

docs/UI-STANDARD.md

---

# 8. Prinsip Backend

Backend wajib mengikuti standar:

- Service Layer
- Policy
- Event
- Listener
- Queue
- Notification
- Observer
- Audit

Seluruh standar backend dijelaskan pada:

docs/BACKEND-STANDARD.md

---

# 9. Standar Modul

Setiap modul harus mempunyai pola yang sama.

Minimal terdiri dari:

- Index
- Create
- Edit
- Show
- Archive
- Activity Log
- Bulk Action
- Permission

Standarnya dijelaskan pada:

docs/MODULE-STANDARD.md

---

# 10. Workflow Standar

Status data menggunakan workflow berikut.

Draft

↓

Review

↓

Approved

↓

Published

↓

Active

↓

Suspended

↓

Archived

↓

Deleted

Tidak boleh membuat status baru tanpa alasan yang jelas.

---

# 11. Audit

Seluruh perubahan data penting wajib dicatat.

Minimal mencatat:

- User
- Waktu
- Nilai Lama
- Nilai Baru
- IP Address
- Device
- Tenant
- Branch

---

# 12. Versioning

Konfigurasi penting harus memiliki versi.

Contoh:

- Paket Internet
- Billing
- Radius
- Provisioning

---

# 13. Performance

Semua modul harus memperhatikan:

- Pagination
- Eager Loading
- Cache
- Queue
- Database Index
- Lazy Loading

---

# 14. Security

Seluruh modul harus menggunakan:

- Authentication
- Authorization
- Policy
- Validation
- CSRF Protection
- Soft Delete
- Audit Log

Tidak boleh melakukan pengecekan role secara hardcode.

---

# 15. Coding Standard

Seluruh kode mengikuti:

- Laravel Best Practice
- PSR-12
- SOLID
- DRY
- KISS
- YAGNI

---

# 16. Definition of Done (DoD)

Sebuah fitur dianggap selesai apabila:

✓ Mengikuti SSOT

✓ Mengikuti UI Standard

✓ Mengikuti Backend Standard

✓ Menggunakan Service Layer

✓ Mendukung Role & Permission

✓ Mendukung Audit Log

✓ Mendukung Soft Delete

✓ Mendukung Restore

✓ Mendukung Bulk Action (jika relevan)

✓ Tidak menghasilkan error JavaScript

✓ Tidak menghasilkan error Livewire

✓ Query database optimal

✓ Responsive

✓ Siap dikembangkan pada sprint berikutnya

# Standar Output AI

Setiap analisis atau implementasi wajib menghasilkan laporan dengan format berikut.

1. Scope
2. Coverage
3. Hasil Analisis
4. Ketidaksesuaian
5. Rencana Perbaikan
6. Implementasi
7. Validasi
8. Status

AI tidak boleh langsung mengubah kode tanpa terlebih dahulu melakukan analisis terhadap ruang lingkup pekerjaan.

# 17. Prioritas Pengembangan

Pengembangan dilakukan berurutan.

1. Fondasi Sistem
2. UI Standard
3. Backend Standard
4. Module Standard
5. Component Standard
6. ISP Module
7. CRM Module
8. Billing Module
9. Finance Module
10. Automation
11. Monitoring
12. Reporting

Tidak dianjurkan melompat ke sprint berikutnya sebelum fondasi selesai.

---

# 18. Perubahan Dokumen

SSOT adalah dokumen resmi proyek.

Perubahan hanya dilakukan apabila:

- Menghasilkan arsitektur yang lebih baik.
- Tidak merusak kompatibilitas.
- Disetujui sebagai perubahan standar proyek.

Seluruh perubahan wajib menaikkan versi dokumen.