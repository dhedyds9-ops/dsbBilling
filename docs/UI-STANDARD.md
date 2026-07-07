# WiFiNan ERP
# UI STANDARD

Version : 1.0
Status  : Official
Reference : docs/SSOT.md

---

# 1. Tujuan

Dokumen ini menjadi standar resmi seluruh tampilan WiFiNan ERP.

Semua halaman wajib mengikuti standar ini.

Tidak diperbolehkan membuat layout, spacing, warna, atau komponen baru tanpa alasan arsitektur yang jelas.

---

# 2. Filosofi UI

WiFiNan ERP mengutamakan:

- Konsisten
- Bersih
- Modern
- Cepat dipahami
- Enterprise
- Mudah digunakan operator

Prioritas:

Konsistensi > Estetika > Variasi

---

# 3. Layout Halaman

Semua halaman menggunakan container berikut.

```html
max-w-7xl mx-auto p-3
```

Tidak diperbolehkan menggunakan:

```
p-5
p-6
p-8
px-8
py-10
```

Spacing utama cukup menggunakan:

```
p-3
```

---

# 4. Spacing

Gunakan hanya skala berikut.

## Margin

```
mb-3
mt-3
ml-3
mr-3
mx-3
my-3
```

## Padding

```
p-3
px-3
py-3
```

## Gap

```
gap-3
```

Hindari:

```
gap-7
gap-8
mb-8
mb-10
```

---

# 5. Card Standard

Semua card menggunakan style berikut.

```html
rounded-xl
border
border-gray-200
bg-white
shadow-sm
p-3
```

Tidak boleh ada style card lain.

---

# 6. Header Halaman

Urutan wajib:

Judul

↓

Deskripsi

↓

Toolbar

Contoh

----------------------------------------------------

Paket Internet

Kelola seluruh paket internet

[ Toolbar ]

----------------------------------------------------

---

# 7. Toolbar Standard

Selalu terdiri dari:

Search

↓

Filter

↓

Reset

↓

Manajemen

↓

View Option

Urutan tidak boleh berubah.

---

# 8. Dropdown Manajemen

Semua aksi utama berada di dalam dropdown.

Contoh

Manajemen

↓

Tambah

↓

Bulk Edit

↓

Bulk Delete

↓

Export

↓

Import

↓

Print

Tidak diperbolehkan banyak tombol di header.

---

# 9. Search

Selalu berada di kiri.

Menggunakan icon search.

Placeholder menjelaskan data yang dicari.

Contoh

Cari nama paket...

Cari pelanggan...

Cari invoice...

---

# 10. Filter

Selalu berada di kanan Search.

Filter mengikuti modul.

Contoh:

Status

Owner

Kategori

Jenis

Cabang

---

# 11. Reset Filter

Selalu menggunakan icon refresh.

Tidak menggunakan tulisan Reset.

---

# 12. Table Standard

Urutan kolom:

Checkbox

↓

Status

↓

Nama

↓

Kategori

↓

Owner

↓

Harga

↓

Update

↓

Action

Kolom seminimal mungkin.

---

# 13. Action

Semua aksi menggunakan dropdown.

Isi standar:

Detail

Edit

Duplicate

Archive

Delete

Restore

Tidak boleh menggunakan 5 tombol sekaligus.

---

# 14. Bulk Action

Semua modul mendukung bila relevan.

Minimal:

Bulk Delete

Bulk Activate

Bulk Deactivate

Bulk Export

Bulk Archive

Bulk Edit

---

# 15. Form Standard

Semua Create/Edit menggunakan struktur berikut.

Card

↓

Section

↓

Field

↓

Action

---

# 16. Form Field

Urutan field:

Label

↓

Input

↓

Helper Text

↓

Validation Error

Tidak boleh berubah.

---

# 17. Wizard

Form kompleks menggunakan Wizard.

Contoh:

Informasi

↓

Konfigurasi

↓

Billing

↓

Provisioning

↓

Review

↓

Simpan

---

# 18. Basic Mode

Operator hanya melihat field penting.

Contoh Paket Internet

Nama

Bandwidth

Harga

Shared User

Status

---

# 19. Advanced Mode

Admin melihat tambahan.

Radius

Queue

Burst

Hotspot

PPPoE

Script

MikroTik Attribute

---

# 20. Preview

Sebelum Save tampilkan Preview.

Contoh

Queue

Radius

PPPoE

Billing

Provisioning

---

# 21. Button Standard

Jenis:

Primary

Secondary

Success

Warning

Danger

Ghost

Outline

Ukuran:

sm

md

lg

Tidak membuat style button baru.

---

# 22. Modal Standard

Ukuran:

Small

Medium

Large

XL

Tidak boleh ukuran lain.

---

# 23. Empty State

Semua modul sama.

Icon

↓

Judul

↓

Deskripsi

↓

Action

---

# 24. Flash Message

Jenis:

Success

Warning

Error

Info

Posisi:

Di atas halaman.

Style konsisten.

---

# 25. Badge

Status:

Hijau

Warning:

Kuning

Error:

Merah

Info:

Biru

Draft:

Abu-abu

---

# 26. Warna

Primary

Blue

Success

Green

Danger

Red

Warning

Yellow

Neutral

Gray

Tidak menggunakan warna lain.

---

# 27. Typography

Heading

```
text-2xl font-bold
```

Sub Heading

```
text-lg font-semibold
```

Body

```
text-sm
```

Caption

```
text-xs
```

---

# 28. Icon

Seluruh aplikasi menggunakan Heroicons.

Tidak mencampur library icon.

---

# 29. Responsive

Semua halaman wajib mendukung:

Desktop

Tablet

Mobile

Tidak boleh ada horizontal scroll kecuali tabel besar.

---

# 30. Reusable Component

Seluruh UI dibuat menjadi komponen reusable.

Contoh:

Button

Card

Input

Modal

Dropdown

Badge

Flash Message

Empty State

Toolbar

Pagination

Table

---

# 31. Konsistensi

Jika terdapat dua halaman dengan fungsi sama, maka tampilannya harus sama.

Contoh:

Create Customer

Create Paket

Create Router

Harus memiliki layout yang identik.

---

# 32. Definition of Done (UI)

UI dianggap selesai apabila:

✓ Mengikuti UI Standard

✓ Menggunakan komponen reusable

✓ Responsive

✓ Konsisten

✓ Tidak ada spacing acak

✓ Tidak ada warna acak

✓ Tidak ada button acak

✓ Tidak ada card berbeda

✓ Tidak ada toolbar berbeda

✓ Tidak ada form berbeda

✓ Tidak ada modal berbeda

✓ Tidak ada JavaScript error

✓ Tidak ada Livewire error

---

END OF DOCUMENT