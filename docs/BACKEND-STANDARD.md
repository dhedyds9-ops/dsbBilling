# WiFiNan ERP
# BACKEND STANDARD

Version : 1.0
Status : Official
Reference :
- docs/SSOT.md
- docs/UI-STANDARD.md

---

# 1. Tujuan

Dokumen ini menjadi standar resmi seluruh backend WiFiNan ERP.

Semua developer dan AI wajib mengikuti struktur yang sama.

Business logic tidak boleh ditempatkan secara acak.

---

# 2. Arsitektur

Seluruh modul wajib mengikuti alur berikut.

Presentation

↓

Blade

↓

Livewire Component

↓

Service Layer

↓

Repository (Opsional)

↓

Model

↓

Database

---

# 3. Tanggung Jawab Setiap Layer

## Blade

Hanya bertugas menampilkan UI.

Tidak boleh berisi:

- Query Database
- Business Logic
- Perhitungan
- Validasi

Blade hanya boleh:

- Menampilkan data
- Loop
- Kondisi sederhana
- Memanggil komponen

---

## Livewire

Livewire hanya mengelola:

- State
- Filter
- Search
- Pagination
- Sorting
- Modal
- Event UI
- Validasi Input

Livewire tidak boleh:

- Menulis query kompleks
- Menyimpan business logic
- Provisioning
- Billing
- Sinkronisasi MikroTik

Semua dipindahkan ke Service.

---

## Service Layer

Service adalah pusat seluruh business logic.

Contoh:

CustomerService

BillingService

ProvisioningService

RouterService

ServiceProfileService

InvoiceService

PaymentService

RadiusService

QueueService

AutomationService

Semua proses bisnis wajib berada di sini.

---

## Repository

Repository digunakan bila:

- Query kompleks
- Banyak join
- Banyak filter
- Digunakan lebih dari satu Service

Jika query masih sederhana, Repository tidak wajib.

---

## Model

Model hanya bertugas:

- Relasi
- Scope
- Accessor
- Mutator

Model tidak boleh menjadi tempat business logic besar.

---

# 4. Struktur Folder

Contoh modul ISP.

app/

    Livewire/

        ISP/

            ServiceProfile/

                Index.php

                Create.php

                Edit.php

                Show.php

    Services/

        ISP/

            ServiceProfileService.php

    Models/

        ISP/

            ServiceProfile.php

---

# 5. Standar CRUD

Setiap modul memiliki method minimal.

Index

Create

Store

Edit

Update

Show

Delete

Restore

Duplicate

Archive

Bulk Action

Activity Log

---

# 6. Validasi

Validasi dilakukan di Livewire.

Rule kompleks boleh dipindahkan ke Service.

Tidak boleh validasi di Blade.

---

# 7. Database Transaction

Semua proses penting wajib menggunakan.

DB::transaction()

Contoh:

Create Customer

Create Invoice

Provisioning

Payment

Bulk Delete

Bulk Update

---

# 8. Event

Semua perubahan penting menggunakan Event.

Contoh:

CustomerCreated

InvoicePaid

PackageUpdated

PackageDeleted

ProvisionStarted

ProvisionFinished

---

# 9. Listener

Listener digunakan untuk proses lanjutan.

Contoh:

Generate PPP

Generate Queue

Generate Radius

Kirim Email

Audit

Notification

---

# 10. Queue

Proses berat wajib menggunakan Queue.

Contoh:

Provisioning

Sinkronisasi Router

Generate Voucher

Export Excel

Import Excel

Notification

Tidak boleh synchronous.

---

# 11. Observer

Observer digunakan untuk.

created()

updated()

deleted()

restored()

forceDeleted()

Observer hanya menangani event model.

---

# 12. Policy

Semua hak akses menggunakan Policy.

Tidak boleh:

if(role == ...)

di Blade.

---

# 13. Permission

Menggunakan Role.

Super Admin

Owner

Admin

Operator

Teknisi

Reseller

Customer

Semua akses melalui Gate atau Policy.

---

# 14. Audit Log

Semua perubahan penting dicatat.

Minimal.

User

Tanggal

Action

Old Value

New Value

IP

Device

Tenant

Branch

---

# 15. Versioning

Konfigurasi penting memiliki versi.

Contoh.

Paket Internet

Billing

Provisioning

Radius

---

# 16. Soft Delete

Semua master data wajib mendukung.

Delete

Restore

Force Delete (Super Admin)

---

# 17. Bulk Action

Standar.

Bulk Delete

Bulk Activate

Bulk Deactivate

Bulk Edit

Bulk Export

Bulk Archive

---

# 18. API

API hanya memanggil Service.

Tidak boleh business logic di Controller API.

---

# 19. Notification

Gunakan Notification Laravel.

Contoh.

Invoice

Suspend

Provision

Approval

Reminder

---

# 20. Logging

Gunakan Log hanya untuk.

Debug

Exception

Monitoring

Jangan menggunakan Log sebagai business logic.

---

# 21. Error Handling

Gunakan.

try

↓

DB Transaction

↓

Rollback

↓

Log Error

↓

Flash Message

↓

Return

---

# 22. Performance

Gunakan.

Eager Loading

Pagination

Cache

Database Index

Queue

Chunk

Cursor

---

# 23. Clean Code

Ikuti.

PSR-12

SOLID

DRY

KISS

YAGNI

Laravel Best Practice

---

# 24. Naming Standard

Service

CustomerService

BillingService

RouterService

ProvisioningService

Model

Customer

Invoice

Router

ServiceProfile

Livewire

Index

Create

Edit

Show

---

# 25. Definition of Done

Backend dianggap selesai apabila.

✓ Mengikuti SSOT

✓ Mengikuti Backend Standard

✓ Business Logic di Service

✓ Menggunakan Transaction

✓ Menggunakan Policy

✓ Menggunakan Audit

✓ Mendukung Soft Delete

✓ Mendukung Restore

✓ Mendukung Event

✓ Mendukung Queue bila diperlukan

✓ Query Optimal

✓ Tidak ada duplikasi kode

✓ Tidak ada error

✓ Siap dikembangkan

---

END OF DOCUMENT