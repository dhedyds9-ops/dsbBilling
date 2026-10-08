# Panduan Manajemen SaaS dsBilling (Multi-Tenant)

Aplikasi dsBilling ini dilengkapi dengan arsitektur **True Multi-Tenant SaaS** menggunakan sistem pergantian *database* SQLite secara dinamis. Artinya, Anda bisa menyewakan aplikasi ini kepada banyak ISP (Klien) hanya dengan 1 (satu) instalasi aplikasi di server, tanpa mencampuradukkan data antar ISP.

Setiap penyewa (klien) akan mendapatkan:
1. File *database* independen (database/tenants/nama_isp.sqlite).
2. Folder *storage* file independen (untuk logo, dll).
3. Akses mandiri sebagai Administrator di subdomainnya masing-masing.

---

## Prosedur Mendaftarkan Pelanggan (ISP) Baru

Setiap kali ada perusahaan ISP baru (contoh: **ISP Maju Jaya** dengan subdomain majujaya.mstore.id) yang ingin menyewa aplikasi Anda, ikuti 4 langkah mudah berikut:

### Langkah 1: Persiapan DNS & Web Server
Pastikan domain atau subdomain klien sudah diarahkan ke IP server (atau Nginx Proxy) Anda.
- **Di DNS (Rumahweb/Cloudflare):** Buat A Record majujaya mengarah ke IP Publik Anda.
- **Di Web Server / Nginx Proxy Manager:** Pastikan subdomain majujaya.mstore.id diteruskan (*forward*) ke IP lokal instalasi dsBilling Anda dengan opsi **Pass Host Header** dalam keadaan aktif.

### Langkah 2: Buat Instance Database Baru
Buka terminal server Anda, masuk ke direktori dsBilling (cd /var/www/dsbilling), dan jalankan perintah sakti ini:

``bash
php artisan tenant:create majujaya --admin-email=admin@majujaya.com --admin-pass=R4h4s1a123
``
*(Perintah ini akan secara otomatis membangun struktur tabel yang masih kosong khusus untuk ISP Maju Jaya dan meng-inject password rahasia yang Anda tentukan).*

### Langkah 3: Perbaiki Hak Akses (SANGAT PENTING!)
Karena perintah di Langkah 2 biasanya dijalankan menggunakan akun oot, Anda **wajib** mengembalikan hak milik folder database & storage kepada *web server* (www-data) agar aplikasi tidak mendapati *Error 500 Read-Only*.

Jalankan kedua perintah ini:
``bash
chown -R www-data:www-data /var/www/dsbilling/database/tenants
chown -R www-data:www-data /var/www/dsbilling/storage/app/public/tenants
``

### Langkah 4: Penyerahan ke Klien (Handover)
Proses selesai! Sekarang Anda bisa memberikan informasi ini ke Klien ISP Anda:
- **URL Akses:** http://majujaya.mstore.id
- **Email Login:** dmin@majujaya.com
- **Password:** R4h4s1a123

Klien tersebut kini bisa masuk dan mengatur kerajaannya sendiri tanpa mengganggu instalasi dsBilling utama Anda maupun milik klien yang lain!
