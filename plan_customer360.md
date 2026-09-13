# Implementasi Customer 360 & Manajemen Pelanggan (Reseller)

## 1. Lingkup Pekerjaan
Kita akan membangun 3 komponen krusial agar Reseller bisa mengelola pelanggannya secara penuh:
- **Create:** Halaman untuk mendaftarkan pelanggan baru.
- **Edit:** Halaman untuk mengubah data profil pelanggan.
- **Customer 360:** Pusat komando (*Dashboard* mini) untuk setiap pelanggan, menampilkan profil, tagihan, layanan internet, dan riwayat aktivitas.

## 2. Strategi Keamanan
- Saat *Create*, sistem akan **secara otomatis menyisipkan eseller_id** milik akun yang sedang login ke database pelanggan.
- Saat *Edit* dan *Customer 360*, sistem akan memblokir akses secara paksa jika pelanggan tersebut bukan milik Reseller (bort(403) atau *redirect* otomatis).

## 3. Rencana Eksekusi
1. Membuat App\Livewire\ResellerPortal\Customer\Create, Edit, dan Customer360 yang mewarisi modul CRM Pusat.
2. Memodifikasi file *blade view* untuk masing-masing agar tombol pembatalan dan navigasi kembali ke Portal Reseller, bukan ke modul Admin Pusat.
3. Memperbarui outes/web.php untuk memetakan jalur URL customers.create, customers.edit, dan customers.show ke komponen-komponen baru ini.
