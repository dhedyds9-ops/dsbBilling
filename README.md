# dsBilling - Enterprise ISP Management System

dsBilling adalah sistem penagihan, CRM, dan *provisioning* komprehensif untuk *Internet Service Provider* (ISP) berskala menengah hingga besar. Sistem ini mengintegrasikan Radius, pengelolaan Mikrotik, Auto-Configuration Server (TR-069 via GenieACS), serta pengelolaan HR dan Kepegawaian.

---

## Daftar Isi
1. [Panduan Instalasi Utama (Web & Database)](#1-panduan-instalasi-utama-dsbilling)
2. [Panduan Instalasi FreeRADIUS (REST API Mode)](#2-panduan-instalasi-freeradius-rest-api-mode)
3. [Panduan Instalasi GenieACS (TR-069)](#3-panduan-instalasi-genieacs-tr-069)
4. [Panduan Integrasi MikroTik (PPP AAA, Hotspot, & Accounting)](#4-panduan-integrasi-mikrotik-ppp-aaa-hotspot--accounting)
5. [Panduan Akses & Portal Pelanggan](#5-panduan-akses--portal-pelanggan)
6. [Perintah Perawatan & Sinkronisasi Sistem](#6-perintah-perawatan--sinkronisasi-sistem)

---

## 1. Panduan Instalasi Utama dsBilling

Panduan ini berisi langkah-langkah standar untuk melakukan *deployment* aplikasi dsBilling (berbasis Laravel) ke VPS atau Server Ubuntu (disarankan Ubuntu 22.04 LTS atau 24.04 LTS).

### 1.1 Persiapan Server (Install Paket yang Dibutuhkan)

Login ke server Ubuntu Anda via SSH, lalu jalankan perintah berikut untuk menginstal Nginx, PHP (minimal 8.2), Composer, dan dependensi lainnya:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git unzip curl supervisor sqlite3 redis-server

# Instalasi Node.js 18 (Wajib untuk kompilasi Frontend & GenieACS)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalasi PHP 8.2 dan ekstensinya
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-sqlite3 php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis

# Instalasi Composer (Package Manager PHP)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 1.2 Kloning Repositori & Install Dependencies

Masuk ke folder web root `/var/www/` dan kloning aplikasi dari GitHub Anda:

```bash
cd /var/www
sudo git clone https://github.com/dhedyds9-ops/dsbBilling.git dsbilling
cd dsbilling

# Buat ulang folder struktur Laravel
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Berikan akses ke folder cache/log
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Instal library backend
sudo composer install --optimize-autoloader --no-dev
sudo cp .env.example .env
sudo php artisan key:generate

# Instalasi & Penyiapan Database MySQL (MariaDB)
sudo apt install -y mariadb-server
sudo systemctl enable mariadb
sudo systemctl start mariadb
sudo mysql -e "CREATE DATABASE dsbilling_db;"
sudo mysql -e "CREATE USER 'dsbilling_user'@'localhost' IDENTIFIED BY 'password_dsbilling';"
sudo mysql -e "GRANT ALL PRIVILEGES ON dsbilling_db.* TO 'dsbilling_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Konfigurasi koneksi MySQL ke file .env
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=dsbilling_db/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=dsbilling_user/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=password_dsbilling/' .env
sed -i '/DB_DATABASE=\/var/d' .env

# Instal library frontend & kompilasi aset (Tailwind & Vite)
npm install
npm run build
```

### 1.3 Eksekusi Database & Build Tampilan

```bash
# Jalankan migrasi tabel MySQL
php artisan migrate --force

# Masukkan data awal (Seeder) & Akun Admin (Catat email & password yang muncul)
php artisan db:seed

# Kompilasi cache
php artisan optimize:clear

# 🚨 PENTING: Kembalikan hak akses ke Web Server (Nginx) agar tidak Error 500
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 1.4 Konfigurasi Nginx (Web Server) & Reverse Proxy

Konfigurasi Nginx untuk dsBilling membutuhkan aturan *Timeout* khusus (300 detik) agar proses berat seperti sinkronisasi GenieACS (TR-069) tidak terputus (Error 504). 

Bagi Anda yang menggunakan 1 Server (Nginx lokal) maupun 2 Server Terpisah (menggunakan Nginx Reverse Proxy / Nginx Proxy Manager), **silakan baca panduan konfigurasinya secara lengkap di sini:**
👉 **[Panduan Lengkap Konfigurasi Nginx & Proxy](docs/NGINX_PROXY_GUIDE.md)**

### 1.5 Setup Pekerja Latar Belakang (Supervisor / Queue) & Cron

```bash
sudo nano /etc/supervisor/conf.d/dsbilling-worker.conf
```
Isi dengan:
```ini
[program:dsbilling-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/dsbilling/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/dsbilling/storage/logs/worker.log
```
Mulai proses:
```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start dsbilling-worker:*
```
Cronjob Tagihan:
```bash
sudo crontab -u www-data -e
# Tambahkan baris berikut:
* * * * * cd /var/www/dsbilling && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Panduan Instalasi FreeRADIUS (REST API Mode)

Aplikasi **dsBilling** dirancang dengan arsitektur modern. Alih-alih membaca database secara lambat, dsBilling menggunakan sistem **RADIUS REST API** (`/api/v1/radius/...`). 

### Metode 1: Instalasi Otomatis (1-Klik via Skrip - Direkomendasikan)

Telah disediakan skrip otomatis yang langsung menginstal paket FreeRADIUS, mengonfigurasi modul REST, mendaftarkan router MikroTik di `clients.conf`, membuka port firewall, serta menonaktifkan filter username (sehingga realm seperti `@dsbilling` didukung tanpa error).

Jalankan perintah berikut di VPS:
```bash
cd /var/www/dsbilling
sudo bash scripts/setup-freeradius.sh
```
> **Catatan:** Skrip akan secara otomatis mendeteksi Shared Secret dari database router Anda. Jika ingin menentukan secret tertentu, Anda dapat menjalankannya dengan parameter:
> `sudo bash scripts/setup-freeradius.sh "rahasia123"`

### Metode 2: Konfigurasi Manual

Jika ingin melakukan konfigurasi secara manual:

#### 2.1 Instalasi Modul
```bash
sudo apt update
sudo apt install -y freeradius freeradius-rest freeradius-utils
sudo ln -sf /etc/freeradius/3.0/mods-available/rest /etc/freeradius/3.0/mods-enabled/rest
```

#### 2.2 Konfigurasi Modul REST
Edit file `/etc/freeradius/3.0/mods-available/rest`:
```text
rest {
    connect_uri = "http://127.0.0.1/api/v1/radius"

    authenticate {
        uri = "${..connect_uri}/preauth"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "password": "%{User-Password}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}"}'
    }

    authorize {
        uri = "${..connect_uri}/authorize"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}"}'
    }

    accounting {
        uri = "${..connect_uri}/accounting"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "status_type": "%{Acct-Status-Type}", "session_id": "%{Acct-Session-Id}", "input_octets": "%{Acct-Input-Octets}", "output_octets": "%{Acct-Output-Octets}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}"}'
    }
}
```

#### 2.3 Konfigurasi Sites-Available Default
Pada file `/etc/freeradius/3.0/sites-available/default`:
1. Masukkan `rest` di dalam blok `authorize { ... }`.
2. Masukkan blok `Auth-Type rest { rest }` di dalam `authenticate { ... }`.
3. Masukkan `rest` di dalam blok `accounting { ... }`.
4. Komentari atau nonaktifkan `filter_username` agar realm seperti `@dsbilling` tidak ditolak:
   ```bash
   sudo sed -i 's/^\s*filter_username/# &/' /etc/freeradius/3.0/sites-available/default
   ```

#### 2.4 Daftarkan NAS / MikroTik di `clients.conf`
Tambahkan di akhir file `/etc/freeradius/3.0/clients.conf`:
```text
client all_mikrotik {
    ipaddr = 0.0.0.0/0
    secret = "rahasia123"
    shortname = mikrotik
    nas_type = other
    require_message_authenticator = auto
}
```

#### 2.5 Buka Port Firewall & Jalankan Service
```bash
sudo ufw allow 1812/udp
sudo ufw allow 1813/udp
sudo ufw allow 3799/udp
sudo systemctl restart freeradius
```

---

## 3. Panduan Instalasi GenieACS (TR-069)

### 3.1 Instalasi MongoDB
Node.js sudah terinstal pada tahap 1. Sekarang instal database MongoDB yang dibutuhkan oleh GenieACS:
```bash
curl -fsSL https://pgp.mongodb.com/server-7.0.asc | sudo gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg --dearmor
echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/7.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-7.0.list
sudo apt-get update && sudo apt-get install -y mongodb-org
sudo systemctl enable mongod && sudo systemctl start mongod
```

### 3.2 Instalasi GenieACS & Konfigurasi
```bash
sudo npm install -g genieacs
sudo useradd --system --no-create-home --user-group genieacs
sudo mkdir -p /opt/genieacs /var/log/genieacs /opt/genieacs/ext
sudo chown genieacs:genieacs /opt/genieacs /var/log/genieacs /opt/genieacs/ext

# Buat file konfigurasi Environment (Salin blok di bawah ini sekaligus)
sudo bash -c 'cat <<EOF > /opt/genieacs/genieacs.env
GENIEACS_CWMP_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-cwmp-access.log
GENIEACS_NBI_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-nbi-access.log
GENIEACS_FS_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-fs-access.log
GENIEACS_UI_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-ui-access.log
GENIEACS_DEBUG_FILE=/var/log/genieacs/genieacs-debug.yaml
GENIEACS_EXT_DIR=/opt/genieacs/ext
GENIEACS_UI_JWT_SECRET=dsBillingSuperSecret123
EOF'
```

### 3.3 Systemd Services (Otomatis)
Jalankan blok perintah berikut untuk membuat dan menjalankan 4 *service* GenieACS sekaligus:
```bash
for service in cwmp nbi fs ui; do
sudo bash -c "cat <<EOF > /etc/systemd/system/genieacs-\$service.service
[Unit]
Description=GenieACS \$service
After=network.target

[Service]
User=genieacs
EnvironmentFile=/opt/genieacs/genieacs.env
ExecStart=/usr/bin/env genieacs-\$service

[Install]
WantedBy=default.target
EOF"
done

sudo systemctl daemon-reload
sudo systemctl enable genieacs-cwmp genieacs-nbi genieacs-fs genieacs-ui
sudo systemctl start genieacs-cwmp genieacs-nbi genieacs-fs genieacs-ui
```

### 3.4 Konfigurasi Integrasi dengan Laravel (.env)

Agar sistem **dsBilling** dapat berkomunikasi dengan GenieACS, tambahkan atau pastikan variabel berikut ada pada file `.env` di folder instalasi Laravel (`/var/www/dsbilling/.env`):

```env
# Sesuaikan dengan pengaturan GenieACS
GENIEACS_BASE_URL=http://localhost:7557
GENIEACS_USERNAME=admin
GENIEACS_PASSWORD=admin
```

---

## 4. Panduan Integrasi MikroTik (PPP AAA, Hotspot, & Accounting)

Agar pelanggan PPPoE dan Hotspot dapat diautentikasi dan sesi aktifnya tersinkronisasi secara *real-time* dengan sistem dsBilling, lakukan konfigurasi pada Router MikroTik melalui Terminal / Winbox:

### 4.1 Tambahkan RADIUS Server di MikroTik
Buka Terminal MikroTik dan jalankan:
```routeros
/radius add address=<IP_VPS_DSBILLING> secret="<RADIUS_SECRET>" service=ppp,hotspot authentication-port=1812 accounting-port=1813 timeout=3000ms comment="dsBilling RADIUS"
```
> Ganti `<IP_VPS_DSBILLING>` dengan IP Public VPS Anda dan `<RADIUS_SECRET>` dengan Secret yang diatur di FreeRADIUS (misal: `dsBillingSecret123`).

### 4.2 Aktifkan CoA / Disconnect Requests (Incoming)
Agar dsBilling dapat memutuskan koneksi pelanggan secara instan (misal saat isolir tagihan atau kick user):
```routeros
/radius incoming set accept=yes port=3799
```

### 4.3 Konfigurasi PPP AAA (PPPoE) & Accounting Otomatis
**Sangat Penting:** Agar status pengguna di dsBilling otomatis berubah menjadi Online saat terhubung dan Offline saat terputus (tanpa harus klik tombol singkronisasi manual), aktifkan fitur `accounting`:
```routeros
/ppp aaa set use-radius=yes accounting=yes interim-update=00:05:00
```
- `accounting=yes`: MikroTik akan mengirimkan sinyal *Accounting-Start* saat konek dan *Accounting-Stop* saat diskonek ke port 1813 FreeRADIUS.
- `interim-update=00:05:00`: MikroTik akan memperbarui catatan kuota/uptime setiap 5 menit ke dsBilling.

### 4.4 Konfigurasi Hotspot Server Profile (Jika menggunakan Hotspot)
Jika Anda menggunakan layanan Hotspot MikroTik:
```routeros
/ip hotspot profile set [find] use-radius=yes radius-accounting=yes radius-interim-update=00:05:00
```

---

## 5. Panduan Akses & Portal Pelanggan

dsBilling menyediakan antarmuka khusus bagi pelanggan untuk mengecek status langganan, melihat tagihan, riwayat pembayaran, hingga tiket komplain.

### 5.1 Alamat / URL Login Pelanggan
Pelanggan dapat mengakses halaman login melalui tautan:
- `https://domain-anda.com/login` atau `https://domain-anda.com/login/customer`

### 5.2 Format Kredensial Login yang Didukung
Pelanggan dapat masuk menggunakan salah satu dari 4 identitas berikut:
1. **Kode Pelanggan / ID Pelanggan**: Contoh `2026101408`
2. **Nomor HP / WhatsApp**: Nomor terdaftar, baik format lokal (`08123...`) maupun internasional (`628123...`)
3. **Username Layanan PPPoE / Hotspot**: Contoh `2026101408@dsbilling`
4. **Alamat Email**: Email yang terdaftar saat registrasi

### 5.3 Password Default Pelanggan
- Password *default* untuk seluruh pelanggan baru yang dibuat melalui sistem atau *Wizard Instalasi Teknisi* adalah:
  **`123456`**
- Pelanggan dapat mengubah password ini kapan saja setelah login dengan mengakses menu **Profil Pelanggan** (`/customer-portal/profile`).

---

## 6. Perintah Perawatan & Sinkronisasi Sistem

dsBilling dilengkapi serangkaian *Artisan Console Commands* untuk memelihara integritas data di semua tenant (Multi-tenant SQLite / MySQL).

### 6.1 Sinkronisasi Kepemilikan Reseller / Mitra (Cascade Ownership)
Jika terdapat data PPPoE, Hotspot, atau Voucher lama yang kepemilikan Resellernya berbeda dengan data Pelanggan induk (misal di tabel pelanggan tertulis Mitra A tapi di tabel PPPoE tertulis Pusat), jalankan perintah ini untuk menyelaraskannya:
```bash
# Sinkronkan untuk seluruh tenant
php artisan isp:sync-reseller-ownership --all-tenants

# Atau untuk 1 tenant tertentu
php artisan isp:sync-reseller-ownership <tenant_domain>
```

### 6.2 Pembersihan Voucher Kadaluarsa (Reap Expired Vouchers)
Untuk menandai voucher yang masa berlakunya sudah habis menjadi status *Expired*:
```bash
php artisan isp:reap-expired-vouchers --all-tenants
```
*(Perintah ini juga otomatis dieksekusi setiap malam oleh Laravel Scheduler / Cronjob)*.

### 6.3 Sinkronisasi Sesi MikroTik Realtime
Untuk menarik sesi aktif PPP / Hotspot dari RouterOS jika accounting RADIUS sempat terputus:
```bash
php artisan mikrotik:sync-sessions
```




