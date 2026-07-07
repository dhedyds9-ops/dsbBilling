# WiFiNan ERP
# COMPONENT STANDARD

Version : 1.0
Status : Official

Reference

- docs/SSOT.md
- docs/UI-STANDARD.md
- docs/BACKEND-STANDARD.md
- docs/MODULE-STANDARD.md

---

# 1. Tujuan

Dokumen ini mendefinisikan seluruh komponen UI yang digunakan oleh WiFiNan ERP.

Semua halaman WAJIB menggunakan komponen ini.

Tidak diperbolehkan membuat UI langsung di Blade apabila sudah tersedia komponen.

---

# 2. Filosofi

Satu komponen.

Digunakan di seluruh sistem.

Perubahan dilakukan pada komponen.

Bukan pada setiap halaman.

---

# 3. Layout

## x-page

Container utama.

Digunakan oleh semua halaman.

Contoh

Dashboard

Customer

Paket

Billing

Finance

Inventory

Router

---

## x-page-header

Header standar.

Berisi

- Judul
- Deskripsi
- Toolbar

---

## x-page-body

Isi halaman.

---

# 4. Card

## x-card

Card standar.

Memiliki:

Header

Body

Footer

---

## x-card-header

Judul card.

---

## x-card-body

Isi card.

---

## x-card-footer

Action card.

---

# 5. Toolbar

## x-toolbar

Toolbar standar.

Berisi.

Search

Filter

Reset

View

Management

---

## x-search

Search standar.

---

## x-filter

Dropdown filter.

---

## x-reset-filter

Reset filter.

---

## x-management-menu

Dropdown Manajemen.

Isi.

Tambah

Bulk Edit

Export

Import

Print

Archive

---

# 6. Table

## x-data-table

Wrapper tabel.

---

## x-table-head

Header tabel.

---

## x-table-body

Isi tabel.

---

## x-table-row

Baris.

---

## x-table-empty

Empty state.

---

## x-pagination

Pagination standar.

---

# 7. Form

## x-form

Wrapper form.

---

## x-form-section

Section.

---

## x-form-group

Label

↓

Input

↓

Helper

↓

Error

---

## x-input

Text.

---

## x-number

Number.

---

## x-textarea

Textarea.

---

## x-select

Dropdown.

---

## x-checkbox

Checkbox.

---

## x-radio

Radio.

---

## x-toggle

Switch.

---

## x-date

Tanggal.

---

## x-file-upload

Upload.

---

# 8. Wizard

## x-wizard

Wizard.

---

## x-step

Step.

---

## x-step-content

Isi.

---

## x-step-navigation

Back

Next

Finish

---

# 9. Button

Gunakan hanya.

## x-button

Variant.

Primary

Secondary

Success

Warning

Danger

Ghost

Outline

Ukuran.

sm

md

lg

---

# 10. Badge

## x-badge

Variant.

Success

Warning

Danger

Info

Primary

Gray

---

# 11. Alert

## x-alert

Variant.

Success

Warning

Danger

Info

---

# 12. Modal

## x-modal

Ukuran.

Small

Medium

Large

XL

---

## x-confirm-dialog

Konfirmasi.

Delete

Restore

Archive

Bulk Delete

---

# 13. Dropdown

## x-dropdown

Standar.

---

## x-dropdown-item

Item.

---

# 14. Empty State

## x-empty-state

Selalu berisi.

Icon

Judul

Deskripsi

Action

---

# 15. Loading

## x-loading

Spinner standar.

---

## x-skeleton

Loading table.

Loading card.

Loading form.

---

# 16. Status

## x-status-badge

Status.

Draft

Review

Approved

Published

Active

Suspended

Archived

Deleted

---

# 17. Flash Message

## x-flash-message

Jenis.

Success

Warning

Error

Info

---

# 18. Activity Timeline

## x-activity-log

Menampilkan.

Tanggal

User

Aktivitas

---

# 19. Audit

## x-audit-table

Menampilkan.

Old Value

↓

New Value

↓

User

↓

Tanggal

---

# 20. Statistik

## x-stat-card

Card dashboard.

Icon

Title

Value

Trend

---

# 21. Chart

## x-chart

Wrapper chart.

---

# 22. Permission

## x-permission

Menampilkan komponen sesuai hak akses.

---

# 23. Reusable

Komponen tidak boleh bergantung pada satu modul.

Contoh.

x-button

Harus dapat digunakan.

Customer

Billing

Finance

Inventory

Router

ISP

HRD

Reporting

---

# 24. Larangan

Tidak boleh membuat:

Button baru

Card baru

Modal baru

Alert baru

Input baru

Table baru

Jika sudah tersedia komponen.

---

# 25. Definition of Done

Komponen dianggap selesai apabila.

✓ Reusable

✓ Responsive

✓ Mendukung Dark Mode (jika diterapkan)

✓ Mendukung Livewire

✓ Mendukung Validation

✓ Konsisten

✓ Mudah dipelihara

✓ Tidak bergantung pada modul tertentu

---

END OF DOCUMENT