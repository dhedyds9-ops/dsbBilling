# WiFiNan ERP
# MODULE STANDARD

Version : 1.0
Status : Official

Reference

- docs/SSOT.md
- docs/UI-STANDARD.md
- docs/BACKEND-STANDARD.md

---

# 1. Tujuan

Dokumen ini menjadi standar seluruh modul WiFiNan ERP.

Semua modul wajib memiliki struktur, alur kerja, dan pengalaman pengguna yang sama.

Tidak diperbolehkan membuat modul dengan pola yang berbeda.

---

# 2. Struktur Modul

Setiap modul minimal memiliki:

Module

├── Dashboard (Opsional)

├── Index

├── Create

├── Edit

├── Show

├── Archive

├── Activity Log

└── Setting (Opsional)

---

# 3. Struktur Folder

Contoh

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

resources/views/

livewire/

isp/

service-profile/

index.blade.php

create.blade.php

edit.blade.php

show.blade.php

---

# 4. Routing

Semua modul memiliki route yang sama.

index

create

store

show

edit

update

archive

restore

delete

---

# 5. Halaman Index

Urutan halaman wajib.

Header

↓

Toolbar

↓

Table

↓

Pagination

↓

Modal

↓

Flash Message

---

# 6. Header

Selalu terdiri dari:

Judul

Deskripsi

Dropdown Manajemen

---

# 7. Toolbar

Selalu memiliki:

Search

↓

Filter

↓

Reset

↓

View Option

↓

Bulk Action

Tidak boleh berubah.

---

# 8. Dropdown Manajemen

Minimal berisi:

Tambah

Bulk Edit

Bulk Delete

Export

Import

Print

Archive

---

# 9. Table

Kolom standar.

Checkbox

Status

Nama

Kategori

Owner

Updated

Action

Tambahkan kolom lain hanya bila memang diperlukan.

---

# 10. Action

Semua action menggunakan dropdown.

Isi:

Detail

Edit

Duplicate

Archive

Delete

Restore

Tidak boleh menggunakan banyak tombol.

---

# 11. Bulk Action

Minimal.

Bulk Delete

Bulk Activate

Bulk Deactivate

Bulk Edit

Bulk Archive

Bulk Export

---

# 12. Create

Urutan.

Header

↓

Form

↓

Preview (bila kompleks)

↓

Button

---

# 13. Edit

Layout sama persis dengan Create.

Tidak boleh berbeda.

---

# 14. Show

Minimal memiliki.

Ringkasan

Informasi

Riwayat

Activity Log

Audit

---

# 15. Archive

Data Soft Delete tampil di sini.

Mendukung.

Restore

Force Delete (Super Admin)

---

# 16. Activity Log

Menampilkan.

Tanggal

User

Aktivitas

Perubahan

IP

Device

---

# 17. Form Standard

Semua form.

Card

↓

Section

↓

Field

↓

Action

---

# 18. Wizard

Gunakan apabila field lebih dari ±20 atau proses terdiri dari beberapa tahap.

Contoh.

Customer

Provisioning

Billing

Router

Paket Internet

---

# 19. Basic Mode

Untuk Operator.

Field penting saja.

---

# 20. Advanced Mode

Untuk Admin.

Seluruh konfigurasi teknis.

---

# 21. Preview

Semua konfigurasi kompleks memiliki Preview sebelum Save.

---

# 22. Validation

Semua modul wajib memiliki.

Client Validation

↓

Server Validation

↓

Business Validation

---

# 23. Permission

Minimal.

View

Create

Update

Delete

Restore

Export

Import

Archive

Approve

---

# 24. Audit

Semua perubahan penting wajib dicatat.

---

# 25. Versioning

Modul konfigurasi wajib mendukung versioning.

Contoh.

Paket

Billing

Radius

Provisioning

---

# 26. Dashboard Modul

Jika modul memiliki dashboard.

Minimal.

Jumlah Data

Aktif

Nonaktif

Grafik

Aktivitas

Ringkasan

---

# 27. Export

Standar.

Excel

PDF

CSV

---

# 28. Import

Minimal.

Excel

CSV

Preview Import

Validasi

Error Report

---

# 29. Notification

Setiap aksi penting menghasilkan notifikasi.

Create

Update

Delete

Restore

Provision

Payment

Approval

---

# 30. Workflow

Standar.

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

---

# 31. Definition of Done

Sebuah modul dianggap selesai apabila.

✓ Mengikuti SSOT

✓ Mengikuti UI Standard

✓ Mengikuti Backend Standard

✓ Mengikuti Module Standard

✓ CRUD lengkap

✓ Bulk Action

✓ Permission

✓ Audit

✓ Activity Log

✓ Responsive

✓ Soft Delete

✓ Restore

✓ Flash Message

✓ Tidak ada JavaScript Error

✓ Tidak ada Livewire Error

✓ Query Optimal

✓ Siap dipakai modul lain

---

END OF DOCUMENT