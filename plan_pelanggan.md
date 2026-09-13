# Implementasi Menu Pelanggan (Reseller Portal)

## 1. Analisis Kebutuhan
Menu Pelanggan di Portal Reseller harus memungkinkan Reseller untuk:
- Melihat daftar semua pelanggannya (PPPoE dan Hotspot).
- Menambah pelanggan baru.
- Mengedit data pelanggan yang sudah ada.
- Melihat detail pelanggan (Customer 360).
- Memastikan **Isolasi Data**: Reseller hanya boleh melihat pelanggannya sendiri.

## 2. Struktur File
Kita akan menduplikasi dan menyesuaikan modul CRM pusat agar lebih aman untuk Reseller.
- **Backend:** App\Livewire\ResellerPortal\Customer\Index
- **View:** esources\views\livewire\reseller-portal\customer\index.blade.php
- **Rute:** Di outes/web.php, kita akan mengubah _placeholder_ rute customers.index menjadi mengarah ke komponen baru ini.

## 3. Isolasi Keamanan Otomatis
Karena model Customer belum menggunakan trait HasResellerScope, kita harus menyematkan filter secara manual di *query* Livewire ResellerPortal:
`php
\ = Customer::where('reseller_id', auth()->id())->orWhere('created_by', auth()->id());
`

## 4. Rencana Eksekusi
1. Membuat *class* Livewire ResellerPortal\Customer\Index.
2. Menyalin tampilan dari crm.customer.index dan menyesuaikan rute tombol (Tambah/Edit) ke rute khusus Reseller.
3. Mendaftarkan komponen ke outes/web.php.
