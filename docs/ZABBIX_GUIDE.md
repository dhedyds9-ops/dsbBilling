# Panduan Lengkap Instalasi Zabbix 7.0 LTS

Panduan ini berisi langkah-langkah untuk menginstal **Zabbix 7.0 LTS** (Terbaru) menggunakan kombinasi **Zabbix Server + Nginx + MySQL (MariaDB)** di sistem operasi Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS. Zabbix ini nantinya akan diintegrasikan dengan portal NOC dsBilling Anda.

---

## Persiapan Sistem (Requirements)
*   **OS:** Ubuntu 22.04 LTS atau 24.04 LTS (Fresh Install).
*   **RAM:** Minimal 4GB (Direkomendasikan 8GB untuk jaringan ISP besar).
*   **CPU:** Minimal 2 Core.
*   **Akses:** Pastikan Anda login ke terminal Linux sebagai oot (ketik: sudo su).

---

## Tahap 1: Memasang Repositori Resmi Zabbix
Kita perlu menambahkan sumber aplikasi resmi Zabbix agar Ubuntu bisa mendownload versi terbarunya. Jalankan perintah ini satu per satu:

\\\ash
# Download paket repo Zabbix 7.0 (Contoh untuk Ubuntu 22.04)
wget https://repo.zabbix.com/zabbix/7.0/ubuntu/pool/main/z/zabbix-release/zabbix-release_7.0-1+ubuntu22.04_all.deb

# Install repo
dpkg -i zabbix-release_7.0-1+ubuntu22.04_all.deb

# Update daftar aplikasi Ubuntu
apt update
\\\

---

## Tahap 2: Install Zabbix Server, Frontend, dan Nginx
Sekarang, kita install mesin utama Zabbix beserta tampilan web-nya.

\\\ash
apt install zabbix-server-mysql zabbix-frontend-php zabbix-nginx-conf zabbix-sql-scripts zabbix-agent -y
\\\

---

## Tahap 3: Membuat Database untuk Zabbix
Zabbix butuh tempat untuk menyimpan semua grafik dan log. Kita akan menginstal MariaDB (MySQL versi lebih ringan).

\\\ash
# Install database MariaDB
apt install mariadb-server -y

# Masuk ke terminal database (ketik perintah di bawah ini)
mysql -u root
\\\

Setelah masuk ke terminal MySQL (MariaDB [(none)]>), jalankan perintah ini baris demi baris:

\\\sql
create database zabbix character set utf8mb4 collate utf8mb4_bin;
create user zabbix@localhost identified by 'password_rahasia_anda';
grant all privileges on zabbix.* to zabbix@localhost;
set global log_bin_trust_function_creators = 1;
quit;
\\\
*(Catatan: Ganti password_rahasia_anda dengan password yang Anda inginkan, misal: zabbix123)*

Sekarang, kita suntikkan struktur tabel bawaan Zabbix ke dalam database yang baru dibuat:
\\\ash
# Perintah ini akan meminta password database yang Anda buat di atas
zcat /usr/share/zabbix-sql-scripts/mysql/server.sql.gz | mysql --default-character-set=utf8mb4 -uzabbix -p zabbix
\\\

Matikan kembali fitur function creators demi keamanan:
\\\ash
mysql -u root -e "set global log_bin_trust_function_creators = 0;"
\\\

---

## Tahap 4: Menghubungkan Zabbix Server ke Database
Zabbix perlu tahu password database yang tadi Anda buat. Buka file konfigurasi Zabbix:

\\\ash
nano /etc/zabbix/zabbix_server.conf
\\\

Cari tulisan DBPassword=. Hapus tanda pagar # di depannya, lalu isikan password Anda.
\\\	ext
DBPassword=password_rahasia_anda
\\\
Simpan file (Ctrl+O, Enter, Ctrl+X).

---

## Tahap 5: Konfigurasi Web Server (Nginx)
Agar Anda bisa membuka web Zabbix dari browser, kita perlu mengatur port-nya. Buka file konfigurasi Nginx Zabbix:

\\\ash
nano /etc/zabbix/nginx.conf
\\\

Hapus tanda pagar # pada baris listen dan server_name (hilangkan pagarnya):
\\\	ext
# Hapus tanda pagar (#) di bawah ini:
listen 8080;
server_name example.com;
\\\
*(Catatan: Di sini diatur ke port 8080 agar tidak bertabrakan dengan port 80 bawaan aplikasi dsBilling jika Anda menggabungkannya dalam satu server).*
Simpan file (Ctrl+O, Enter, Ctrl+X).

---

## Tahap 6: Nyalakan Mesin Zabbix!
Mulai (start) dan buat Zabbix menyala otomatis saat server restart (enable):

\\\ash
systemctl restart zabbix-server zabbix-agent nginx php8.1-fpm
systemctl enable zabbix-server zabbix-agent nginx php8.1-fpm
\\\
*(Catatan: Ganti php8.1-fpm menyesuaikan versi PHP bawaan Ubuntu Anda, misal Ubuntu 24.04 menggunakan php8.3-fpm)*

---

## Tahap Akhir: Setup di Browser (Web UI)
1. Buka browser di laptop Anda, ketikkan IP Server Zabbix Anda dan tambahkan port yang Anda set tadi:
   **http://IP-SERVER-ANDA:8080**
2. Halaman *Welcome to Zabbix* akan muncul. Klik **Next step**.
3. Sistem akan mengecek kompatibilitas (semua harus berstatus hijau "OK"). Klik **Next step**.
4. Di halaman Database, masukkan password database MySQL yang tadi Anda buat (password_rahasia_anda). Klik **Next step**.
5. Isi nama server (misal: "NOC Zabbix Server"), zona waktu (*Asia/Jakarta*). Klik **Next**.
6. Selesai!

## Login Pertama (PENTING)
Setelah selesai setup, Anda akan diarahkan ke halaman Login. Gunakan kredensial bawaan pabrik ini:

*   **Username:** Admin *(A-nya harus huruf besar)*
*   **Password:** zabbix *(semua huruf kecil)*

Selesai! Zabbix sekarang sudah terinstal. Anda bisa mulai memasukkan IP Router/OLT Anda ke dalam Zabbix, lalu mengambil Host ID-nya untuk disalin ke kolom zabbix_host_id pada portal admin dsBilling.
