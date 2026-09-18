# dsBilling - Enterprise ISP Management System

dsBilling adalah sistem penagihan, CRM, dan *provisioning* komprehensif untuk *Internet Service Provider* (ISP) berskala menengah hingga besar. Sistem ini mengintegrasikan Radius, pengelolaan Mikrotik, Auto-Configuration Server (TR-069 via GenieACS), serta pengelolaan HR dan Kepegawaian.

---

## Daftar Isi
1. [Panduan Instalasi Utama (Web & Database)](#1-panduan-instalasi-utama-dsbilling)
2. [Panduan Instalasi FreeRADIUS (REST API Mode)](#2-panduan-instalasi-freeradius-rest-api-mode)
3. [Panduan Instalasi GenieACS (TR-069)](#3-panduan-instalasi-genieacs-tr-069)

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

# Set default database ke SQLite untuk kemudahan instalasi
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env

# Instal library frontend & kompilasi aset (Tailwind & Vite)
npm install
npm run build
```

### 1.3 Eksekusi Database & Build Tampilan

```bash
# Buat file database SQLite (Jika menggunakan MySQL, lewati bagian ini dan edit .env)
sudo touch database/database.sqlite
sudo chown www-data:www-data database/database.sqlite

# Jalankan migrasi database
sudo php artisan migrate --force

# Masukkan data awal (Seeder) & Akun Admin (Catat email & password yang muncul)
sudo php artisan db:seed

# Kompilasi cache
sudo php artisan optimize
sudo php artisan view:cache
```

### 1.4 Konfigurasi Nginx (Web Server)

```bash
sudo nano /etc/nginx/sites-available/dsbilling
```
*(Isi konfigurasi standar Nginx Laravel yang mengarah ke `/var/www/dsbilling/public` dan PHP 8.2 FPM).*

```bash
sudo ln -s /etc/nginx/sites-available/dsbilling /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

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
# Tambahkan: * * * * * cd /var/www/dsbilling && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Panduan Instalasi FreeRADIUS (REST API Mode)

Aplikasi **dsBilling** dirancang dengan arsitektur modern. Alih-alih membaca database secara lambat, dsBilling menggunakan sistem **RADIUS REST API**. 

### 2.1 Instalasi Modul
```bash
sudo apt update
sudo apt install -y freeradius freeradius-rest freeradius-utils
sudo ln -s /etc/freeradius/3.0/mods-available/rest /etc/freeradius/3.0/mods-enabled/
```

### 2.2 Konfigurasi Modul REST
`sudo nano /etc/freeradius/3.0/mods-enabled/rest`

```text
rest {
    connect_uri = "http://localhost/api/radius" 

    authenticate {
        uri = "${..connect_uri}/accounting/preauth"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "password": "%{User-Password}", "nas_ip": "%{NAS-IP-Address}", "nas_secret": "%{Client-Secret}"}'
    }
    authorize {
        uri = "${..connect_uri}/accounting/authorize"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "nas_ip": "%{NAS-IP-Address}", "nas_secret": "%{Client-Secret}"}'
    }
    accounting {
        uri = "${..connect_uri}/accounting/ingest"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "status_type": "%{Acct-Status-Type}", "session_id": "%{Acct-Session-Id}", "input_octets": "%{Acct-Input-Octets}", "output_octets": "%{Acct-Output-Octets}", "nas_ip": "%{NAS-IP-Address}", "nas_secret": "%{Client-Secret}"}'
    }
}
```

### 2.3 Aktifkan di Sites-Enabled
Di `/etc/freeradius/3.0/sites-enabled/default`, tambahkan `rest` di bawah blok `authorize`, `authenticate`, dan `accounting`.

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
