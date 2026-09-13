# Implementasi Modul PPPoE & Hotspot (Portal Reseller)

## 1. Analisis Kebutuhan
Reseller membutuhkan akses untuk mengatur rahasia internet pelanggan (Username & Password PPPoE/Hotspot), melakukan sinkronisasi dengan *router* pusat, dan memantau status sesi internet (Aktif/Mati).

## 2. Struktur Arsitektur
Kita akan membuat salinan cerdas dari modul ISP pusat, namun khusus untuk Reseller:
- **PPPoE Backend:** App\Livewire\ResellerPortal\Customer\Pppoe
- **Hotspot Backend:** App\Livewire\ResellerPortal\Customer\Hotspot
- Keduanya akan melakukan injeksi *Hard-Filter* pada kolom eseller_id di model PPPoEUser dan HotspotUser.

## 3. Isolasi Keamanan Otomatis (Security Hard-Filter)
`php
\ = auth()->id();
\ = PPPoEUser::with(['customer', 'serviceProfile', 'router'])
    ->where('reseller_id', \)
    ->orWhere('created_by', \);
`
Dengan filter mutlak ini, Reseller A tidak akan pernah bisa melihat, menghapus, apalagi mengganti *password* koneksi internet milik pelanggan Reseller B.

## 4. Rencana Eksekusi
1. Membuat *class* komponen Livewire untuk PPPoE dan Hotspot di dalam direktori ResellerPortal/Customer/.
2. Menyalin *view* (tampilan antarmuka) dari modul ISP/PPPoEUser milik Administrator, menghapus fitur-fitur berbahaya (seperti ekspor massal ke *router* lain, impor massal tanpa validasi).
3. Mendaftarkan komponen ke outes/web.php untuk menggantikan rute _Coming Soon_.

