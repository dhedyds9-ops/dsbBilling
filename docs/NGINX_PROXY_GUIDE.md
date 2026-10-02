# Panduan Nginx & SSL untuk dsBilling

Panduan ini dirancang agar mudah dipahami oleh pemula sekalipun. 
Pilih **salah satu skenario** di bawah ini yang paling sesuai dengan kondisi server Anda:

---

## 🟢 SKENARIO 1: Anda Hanya Menyewa 1 Server (Disarankan untuk Pemula)
*Pilih skenario ini jika Anda menginstal dsBilling langsung di sebuah VPS (Cloud), dan domain Anda (misal demo.mstore.id) langsung menunjuk ke IP VPS tersebut.*

### Langkah 1: Memasang Konfigurasi dsBilling
Buka terminal server Anda dan jalankan perintah ini:
`bash
sudo nano /etc/nginx/sites-available/dsbilling
`

*Copy-paste* kode di bawah ini. (**Penting:** Ubah tulisan demo.mstore.id dengan domain Anda yang asli):

`nginx
server {
    listen 80;
    # 🔴 UBAH INI: Ganti dengan domain Anda
    server_name demo.mstore.id;

    root /var/www/dsbilling/public;
    index index.php index.html;

    # 🔴 WAJIB: Batas waktu 5 menit agar Sinkronisasi OLT tidak error 504
    client_max_body_size 100M;
    proxy_read_timeout 300;
    proxy_connect_timeout 300;
    proxy_send_timeout 300;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        # Pastikan ini sesuai dengan versi PHP yang terinstal (8.1 / 8.2 / 8.3)
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; 
        
        # Kesabaran PHP-FPM
        fastcgi_read_timeout 300; 
        
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
`
Tekan **Ctrl+O** (Enter) untuk menyimpan, lalu **Ctrl+X** untuk keluar.

### Langkah 2: Mengaktifkan Konfigurasi
Jalankan perintah ini secara berurutan:
`bash
# Menghapus pengaturan bawaan agar tidak bentrok
sudo rm /etc/nginx/sites-enabled/default 

# Mengaktifkan pengaturan dsBilling
sudo ln -s /etc/nginx/sites-available/dsbilling /etc/nginx/sites-enabled/
sudo systemctl restart nginx
`

### Langkah 3: Memasang SSL (Gembok Hijau HTTPS)
Agar aman, jalankan 2 perintah ini:
`bash
sudo apt update && sudo apt install -y certbot python3-certbot-nginx

# 🔴 UBAH INI: Ganti dengan domain Anda
sudo certbot --nginx -d demo.mstore.id
`
*Saat muncul pertanyaan di layar:*
- Masukkan Email Anda lalu *Enter*.
- Ketik **Y** lalu *Enter*.
- Jika disuruh memilih angka, pilih angka **2 (Redirect)** lalu *Enter*.

**🎉 SELESAI! Web dsBilling Anda sudah bisa diakses dengan aman.**

---
---

## 🟠 SKENARIO 2: Anda Memiliki 2 Server (Menggunakan Reverse Proxy)
*Pilih skenario ini jika server dsBilling Anda berada di mesin lokal kantor (CT113), dan Anda menggunakan mesin terpisah (Proxy) untuk mengarahkan domain internet ke mesin kantor Anda.*

### Langkah 1: Pengaturan di Server dsBilling (CT113)
Di terminal server kantor Anda, ketik:
`bash
sudo nano /etc/nginx/sites-available/dsbilling
`
Isi kodenya **sama persis dengan Skenario 1 di atas**, namun pada baris server_name, isi dengan:
`nginx
    server_name localhost 127.0.0.1;
`
Lalu aktifkan:
`bash
sudo ln -s /etc/nginx/sites-available/dsbilling /etc/nginx/sites-enabled/
sudo systemctl restart nginx
`

### Langkah 2: Pengaturan di Server Proxy (Server Penengah)
Beralihlah ke terminal server Proxy Anda. Jika Anda menggunakan **Nginx Proxy Manager (NPM)** yang memiliki tampilan Web, ini sangat mudah:
1. Buka Web NPM Anda > menu **Proxy Hosts** > Edit domain Anda.
2. Isi *Forward IP* dengan IP lokal/publik Server CT113 Anda.
3. Masuk ke Tab **Advanced**, lalu ketikkan 4 baris anti-error ini:
   `nginx
   proxy_read_timeout 300;
   proxy_connect_timeout 300;
   proxy_send_timeout 300;
   send_timeout 300;
   `
4. Masuk ke Tab **SSL**, pilih *Request a new SSL*, centang *Force SSL*, lalu tekan **Save**. Selesai!

*(Jika Anda tidak menggunakan NPM dan mengatur proxy secara manual via terminal CLI, Anda cukup menambahkan 4 baris aturan proxy_..._timeout 300; di atas ke dalam blok location / di file Nginx server proxy Anda).*
