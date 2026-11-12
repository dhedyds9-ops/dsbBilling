# Panduan Penambahan Pelanggan / Tenant ISP Baru (Multi-Tenant dsBilling)

Dokumen ini menjelaskan alur resmi dan langkah teknis saat ada **ISP baru yang ingin berlangganan server dsBilling (SaaS Tenant)**.

---

## 1. Arsitektur Multi-Tenant dsBilling

dsBilling dirancang dengan arsitektur **Dynamic Multi-Tenant SQLite Database Isolation**:
1. **Database Terisolasi Fisik:** Setiap ISP memiliki file database tersendiri di `database/tenants/{nama_isp}.sqlite`. Data antar-ISP tidak bercampur di tabel yang sama.
2. **Storage Terisolasi:** Logo, foto KTP pelanggan, bukti bayar, dan lampiran tiket disimpan di `storage/app/public/tenants/{nama_isp}/`.
3. **Resolusi Domain Dinamis:** Sistem mengenali tenant secara otomatis berdasarkan subdomain yang diakses (contoh: `nusantara.domainanda.com` -> tenant `nusantara`).

---

## 2. Langkah Demi Langkah: Onboarding Tenant ISP Baru

### Langkah 1: Jalankan Perintah Pembuatan Tenant Otomatis
Buka terminal server VPS dsBilling Anda, masuk ke direktori proyek, lalu jalankan Artisan command:

```bash
cd /var/www/dsbilling
php artisan tenant:create {nama_subdomain} --admin-email={email_isp} --admin-pass={password_admin}
```

**Contoh Riil:**
```bash
php artisan tenant:create nusantara --admin-email=admin@nusantara.net.id --admin-pass=AdminNusantara2026!
```

> **Apa yang otomatis dilakukan oleh perintah ini?**
> 1. Membuat file database baru: `database/tenants/nusantara.sqlite`
> 2. Menjalankan seluruh migrasi skema tabel (`migrate --force`)
> 3. Membuat folder media: `storage/app/public/tenants/nusantara/`
> 4. Mengisi data master bawaan (Role Administrator, tipe profil, bagan akun keuangan, voucher template)
> 5. Mendaftarkan user administrator pertama dengan username: `admin`, email: `admin@nusantara.net.id`, dan password yang ditentukan.

---

### Langkah 2: Perbarui Hak Akses File (Wajib di Linux / VPS)
Agar Nginx dan PHP-FPM (`www-data`) dapat membaca dan menulis database serta mengunggah gambar/logo untuk tenant baru:

```bash
sudo chown -R www-data:www-data database/tenants storage bootstrap/cache
sudo chmod -R 775 database/tenants storage bootstrap/cache
```

---

### Langkah 3: Konfigurasi DNS & Web Server (Nginx)

#### A. Pengaturan DNS (Cloudflare / Penyedia Domain)
Arahkan subdomain ISP baru ke IP Public VPS server Anda:
- **Tipe:** `A`
- **Name:** `nusantara` (atau subdomain yang dipilih)
- **Target IP:** `IP_SERVER_VPS_ANDA`

*(Tips Efisien: Anda juga bisa membuat wildcard DNS `*.domainanda.com` mengarah ke IP VPS. Dengan wildcard DNS, Anda tidak perlu menambah DNS baru setiap kali ada ISP yang mendaftar).*

#### B. Pengaturan Nginx
Jika Nginx Anda belum menggunakan wildcard domain, tambahkan subdomain baru pada baris `server_name` di file Nginx dsBilling (`/etc/nginx/sites-available/dsbilling`):

```nginx
server {
    listen 80;
    server_name demo.domainanda.com nusantara.domainanda.com;
    ...
```

Atau jika menggunakan wildcard:
```nginx
server_name *.domainanda.com domainanda.com;
```

Lalu reload Nginx:
```bash
sudo nginx -t && sudo systemctl reload nginx
```

#### C. Pasang Sertifikat SSL (HTTPS)
Terbitkan sertifikat Let's Encrypt untuk subdomain baru:
```bash
sudo certbot --nginx -d nusantara.domainanda.com
```

---

### Langkah 4: Serahkan Akses Login ke Klien ISP

Berikan informasi akses kepada pihak ISP penyewa:
- **URL Web Portal:** `https://nusantara.domainanda.com/login`
- **Username:** `admin` (atau email `admin@nusantara.net.id`)
- **Password:** Password yang dibuat pada Langkah 1

---

### Langkah 5: Checklist Setup Awal untuk ISP Tenant Baru

Setelah pihak ISP berhasil login pertama kali, mereka dapat melakukan setup awal operasional:
1. **Pengaturan Identitas & Branding:**
   - Masuk ke menu **Pengaturan / Branding**.
   - Isi nama ISP, alamat kantor, nomor telepon customer service, dan upload logo ISP. Logo ini otomatis tampil di dashboard, invoice, dan kwitansi.
2. **Koneksi Router MikroTik:**
   - Masuk ke menu **Jaringan / Router**.
   - Daftarkan IP router MikroTik, port API (default 8728), username, dan password API MikroTik.
3. **Konfigurasi Paket Layanan:**
   - Masuk ke menu **Layanan / Paket Internet** untuk membuat profil paket PPPoE atau Hotspot yang dijual oleh ISP tersebut.
4. **Registrasi User Internal:**
   - Daftarkan akun untuk teknisi, kasir, atau reseller melalui menu **Manajemen Pengguna**.

---

## 3. Pemeliharaan & Update Skema Database di Masa Depan

Jika di kemudian hari sistem dsBilling mendapatkan pembaruan fitur atau penambahan kolom database dari tim developer, Anda cukup menjalankan migrasi untuk seluruh tenant sekaligus:

```bash
# Memigrasi seluruh database ISP tenant yang ada di database/tenants/
php artisan tenant:migrate

# Atau jika ingin memigrasi satu tenant tertentu saja:
php artisan tenant:migrate nusantara
```

---

## 4. Troubleshooting Cepat

| Kendala | Penyebab | Solusi |
| :--- | :--- | :--- |
| **Error `attempt to write a readonly database`** | Izin file SQLite belum diizinkan untuk `www-data` | Jalankan `sudo chown -R www-data:www-data database/tenants` dan `sudo chmod -R 775 database/tenants` |
| **Muncul halaman tenant default / mstore** | Subdomain tidak terdeteksi oleh Laravel | Pastikan proxy Nginx meneruskan header `proxy_set_header Host $host;` dan `proxy_set_header X-Forwarded-Host $host;` |
| **Database tenant sudah ada** | Subdomain dengan nama tersebut sudah pernah dibuat | Gunakan nama subdomain lain, atau hapus file `.sqlite` lama jika memang ingin mereset ulang |
