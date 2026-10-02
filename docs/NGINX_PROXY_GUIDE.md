# Panduan Lengkap Konfigurasi Nginx untuk dsBilling

Jika Anda menggunakan arsitektur server tingkat lanjut (misalnya menggunakan satu server khusus untuk aplikasi dsBilling, dan satu server lain khusus untuk Nginx Reverse Proxy / Load Balancer), berikut adalah panduan lengkap konfigurasinya.

---

## 1. Konfigurasi Nginx di Server dsBilling (Lokal / CT113)

Server ini bertugas langsung mengeksekusi kode PHP (PHP-FPM) Laravel. Nginx di sini bertindak sebagai *Web Server* lokal.

Buat atau edit file:
`bash
sudo nano /etc/nginx/sites-available/dsbilling
`

Isi dengan konfigurasi berikut (Sesuaikan versi PHP-FPM dengan yang Anda gunakan, misal 8.2):

`nginx
server {
    listen 80;
    server_name localhost 127.0.0.1; # Atau IP lokal server ini

    root /var/www/dsbilling/public;
    index index.php index.html;

    # Agar Livewire & GenieACS tidak terputus (504 Timeout) saat proses berat
    client_max_body_size 100M;
    proxy_read_timeout 300;
    proxy_connect_timeout 300;
    proxy_send_timeout 300;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; # Sesuaikan versi PHP
        
        # Kesabaran PHP-FPM agar tidak 504 Timeout
        fastcgi_read_timeout 300; 
        
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
`

Aktifkan dan *restart*:
`bash
sudo ln -s /etc/nginx/sites-available/dsbilling /etc/nginx/sites-enabled/
sudo systemctl restart nginx
`

---

## 2. Konfigurasi Nginx di Server Reverse Proxy (Terpisah)

Server terpisah ini biasanya yang memegang domain (misal demo.mstore.id) dan Sertifikat SSL (HTTPS). Tugasnya adalah meneruskan ( *proxying* ) lalu lintas pengguna ke Server dsBilling lokal (CT113).

Jika menggunakan Nginx manual, buat file:
`bash
sudo nano /etc/nginx/sites-available/demo.mstore.id
`

Isi dengan konfigurasi berikut:

`nginx
server {
    listen 80;
    server_name demo.mstore.id;
    
    # Redirect HTTP ke HTTPS (Opsional jika sudah ada SSL)
    # return 301 https://$host$request_uri;

    # Jika ada SSL, buka baris berikut:
    # listen 443 ssl;
    # ssl_certificate /path/to/cert.pem;
    # ssl_certificate_key /path/to/key.pem;

    location / {
        # GANTI dengan IP lokal/publik Server dsBilling (CT113) Anda
        proxy_pass http://192.168.1.100; 
        
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # 🚨 WAJIB: Batas waktu 5 Menit agar tidak 504 Gateway Timeout
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
        send_timeout 300;
    }
}
`

Aktifkan dan *restart*:
`bash
sudo ln -s /etc/nginx/sites-available/demo.mstore.id /etc/nginx/sites-enabled/
sudo systemctl restart nginx
`

### Solusi Praktis: Menggunakan Nginx Proxy Manager (NPM)
Jika server Proxy terpisah Anda menggunakan **Nginx Proxy Manager (Web UI)**, Anda tidak perlu mengetik konfigurasi di atas secara manual:
1. Buka NPM > **Proxy Hosts** > Tambah/Edit Domain Anda.
2. Isi **Forward Hostname / IP** dengan IP Server dsBilling.
3. Masuk ke Tab **Advanced**, lalu *copy-paste* aturan batas waktu berikut:
   `nginx
   proxy_read_timeout 300;
   proxy_connect_timeout 300;
   proxy_send_timeout 300;
   send_timeout 300;
   `
4. Klik **Save**.

---

## 3. Panduan Membuat & Memasang SSL (HTTPS) Gratis

Agar aplikasi dsBilling Anda aman dan gembok hijau menyala (HTTPS), Anda wajib memasang sertifikat SSL. Jika Anda menggunakan server Nginx Terpisah (Manual CLI), ikuti langkah Certbot berikut.

### Langkah 3.1: Instalasi Certbot (Let's Encrypt)
Jalankan perintah ini di **Server Reverse Proxy** (server yang memegang domain):
`bash
sudo apt update
sudo apt install -y certbot python3-certbot-nginx
`

### Langkah 3.2: Buat Sertifikat SSL Otomatis
Jalankan perintah ini untuk menerbitkan sertifikat dan membiarkan Certbot mengedit konfigurasi Nginx Anda secara otomatis:
`bash
# Ganti demo.mstore.id dengan domain asli Anda
sudo certbot --nginx -d demo.mstore.id
`

Saat ditanya, pilih:
- Masukkan Email Anda (untuk peringatan pembaruan SSL).
- Tekan **Y** untuk menyetujui syarat layanan.
- Pilih **2 (Redirect)** ketika ditanya apakah ingin melempar ( *redirect* ) HTTP ke HTTPS.

### Langkah 3.3: (Opsional) Mengaktifkan Perpanjangan Otomatis
Sertifikat Let's Encrypt berlaku 90 hari. Agar diperpanjang otomatis, tes proses perpanjangannya:
`bash
sudo certbot renew --dry-run
`
Jika sukses, SSL Anda akan diperpanjang otomatis setiap 60 hari tanpa perlu disentuh lagi!

*(Catatan: Jika Anda menggunakan **Nginx Proxy Manager**, pembuatan SSL jauh lebih mudah. Cukup masuk ke Tab **SSL** saat mengedit Proxy Host, pilih "Request a new SSL Certificate", centang "Force SSL", setujui ToS, lalu tekan Save).*
