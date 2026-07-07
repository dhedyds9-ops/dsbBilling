# WiFiNan ERP
# AI WORKFLOW

Version : 1.0
Status  : Official
Reference :
- docs/SSOT.md
- docs/UI-STANDARD.md
- docs/BACKEND-STANDARD.md
- docs/MODULE-STANDARD.md
- docs/COMPONENT-STANDARD.md

---

# 1. Tujuan

Dokumen ini menjadi standar kerja AI Assistant pada seluruh pengembangan WiFiNan ERP.

AI wajib mengikuti workflow ini sebelum melakukan analisis, implementasi, refactoring, maupun penambahan fitur.

Workflow ini memastikan seluruh hasil pekerjaan tetap konsisten dengan SSOT dan standar proyek.

---

# 2. Aturan Utama

Sebelum menulis atau mengubah kode, AI wajib membaca dan mengikuti dokumen berikut secara berurutan:

1. docs/SSOT.md
2. docs/UI-STANDARD.md
3. docs/BACKEND-STANDARD.md
4. docs/MODULE-STANDARD.md
5. docs/COMPONENT-STANDARD.md

Dokumen tersebut merupakan acuan utama.

AI tidak boleh membuat implementasi yang bertentangan dengan dokumen tersebut.

---

# 3. Ruang Lingkup

AI hanya boleh mengerjakan modul yang diminta.

Tidak boleh:

- Mengubah modul lain tanpa diminta.
- Menambahkan fitur di luar scope.
- Melakukan refactoring besar tanpa alasan.
- Mengubah database apabila tidak diperlukan.
- Mengubah business process yang sudah berjalan.

---

# 4. Workflow Wajib

Setiap pekerjaan wajib mengikuti urutan berikut.

STEP 1

Analisis modul.

Pahami:

- Struktur folder
- Livewire
- Service
- Model
- View
- Database
- Relasi

Jangan langsung mengubah kode.

---

STEP 2

Bandingkan implementasi dengan:

- SSOT
- UI Standard
- Backend Standard
- Module Standard
- Component Standard

Identifikasi seluruh ketidaksesuaian.

---

STEP 3

Buat daftar masalah.

Pisahkan menjadi:

UI

Backend

Database

Business Logic

Performance

Security

Maintainability

---

STEP 4

Susun rencana implementasi.

Prioritas:

Critical

↓

High

↓

Medium

↓

Low

Jangan langsung mengubah seluruh sistem sekaligus.

---

STEP 5

Implementasi.

Perubahan dilakukan bertahap.

Setiap perubahan harus:

- Tidak merusak fitur lama.
- Tidak mengubah business flow.
- Menggunakan standar proyek.

---

STEP 6

Gunakan komponen reusable.

Sebelum membuat komponen baru, AI wajib mencari apakah komponen serupa sudah tersedia.

Jika ada:

Gunakan.

Jika belum ada:

Baru membuat komponen reusable.

Tidak boleh membuat komponen duplikat.

---

STEP 7

Backend.

Seluruh business logic berada pada Service Layer.

Livewire hanya:

- State
- Validation ringan
- Event
- UI Interaction

Blade hanya:

- Tampilan

Model:

- Relasi
- Scope
- Accessor
- Mutator

---

STEP 8

Validasi.

Pastikan:

- Tidak ada error PHP
- Tidak ada error JavaScript
- Tidak ada error Livewire
- Tidak ada query N+1
- Tidak ada kode duplikat

---

STEP 9

Laporan.

Setelah implementasi selesai, AI wajib membuat laporan.

Format:

## Analisis

...

## Masalah

...

## Perbaikan

...

## File Diubah

...

## Dampak

...

## Checklist

☑ SSOT

☑ UI Standard

☑ Backend Standard

☑ Module Standard

☑ Component Standard

☑ Tidak merusak fitur lama

☑ Responsive

☑ Tidak ada error Livewire

☑ Tidak ada error JavaScript

---

# 5. Aturan Refactoring

Refactoring hanya boleh dilakukan apabila:

- Mengurangi kompleksitas.
- Menghilangkan duplikasi.
- Memperbaiki performa.
- Meningkatkan konsistensi.

Tidak boleh mengubah perilaku sistem.

---

# 6. Aturan Penambahan Fitur

Fitur baru harus:

Mengikuti Module Standard.

Menggunakan UI Standard.

Menggunakan Backend Standard.

Menggunakan Component Standard.

Tidak boleh membuat pola baru apabila pola lama masih sesuai.

---

# 7. Aturan UI

AI wajib menggunakan komponen yang sudah tersedia.

Contoh:

- Button
- Card
- Modal
- Input
- Badge
- Flash Message
- Toolbar
- Table
- Dropdown
- Pagination

Tidak boleh membuat versi lain.

---

# 8. Aturan Backend

AI wajib menggunakan:

Service

Policy

Observer

Event

Listener

Queue

Notification

Audit

Transaction

Tidak boleh memindahkan business logic ke Blade atau Livewire.

---

# 9. Aturan Database

Tidak boleh mengubah struktur database tanpa alasan yang jelas.

Migration harus:

- Aman
- Reversible
- Menggunakan Foreign Key
- Menggunakan Index
- Mendukung Soft Delete bila relevan

---

# 10. Aturan Performance

Selalu gunakan:

- Pagination
- Eager Loading
- Cache
- Queue
- Lazy Loading
- Database Index

Hindari:

- Query berulang
- N+1 Query
- Load seluruh data tanpa pagination

---

# 11. Aturan Security

Seluruh fitur wajib:

- Authentication
- Authorization
- Validation
- Policy
- CSRF Protection
- Audit Log

Tidak boleh hardcode role.

---

# 12. Aturan Dokumentasi

Apabila implementasi mengubah arsitektur atau standar proyek, AI wajib menyarankan pembaruan dokumen yang relevan.

Urutan pembaruan:

SSOT

↓

UI Standard

↓

Backend Standard

↓

Module Standard

↓

Component Standard

↓

AI Workflow

---

# 13. Definition of Done (AI)

AI hanya boleh menyatakan pekerjaan selesai apabila:

✓ Mengikuti SSOT

✓ Mengikuti UI Standard

✓ Mengikuti Backend Standard

✓ Mengikuti Module Standard

✓ Mengikuti Component Standard

✓ Mengikuti AI Workflow

✓ Tidak merusak fitur lama

✓ Tidak ada error PHP

✓ Tidak ada error JavaScript

✓ Tidak ada error Livewire

✓ Query optimal

✓ UI konsisten

✓ Business Logic berada pada Service Layer

✓ Menggunakan komponen reusable

✓ Siap dikembangkan pada sprint berikutnya

---

END OF DOCUMENT