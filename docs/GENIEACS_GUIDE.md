# Panduan Lengkap Jaringan & Domain GenieACS

Dokumen ini merupakan panduan komprehensif tentang bagaimana cara mengatur arsitektur jaringan, *reverse proxy* (Nginx), dan *port forwarding* untuk GenieACS. Panduan ini sangat berguna jika di kemudian hari Anda melakukan instalasi ulang atau memindahkan GenieACS ke server lain.

---

## 1. Konsep Dasar Port GenieACS
GenieACS memiliki 4 *service* utama, namun hanya 2 yang perlu Anda ekspos ke dunia luar (melalui IP Publik atau Domain):

1. **Port 3000 (UI)**: Web antarmuka untuk manusia (Admin ISP). Di sinilah Anda melihat grafik, membuat skrip *Provisioning*, dan melakukan *debugging* modem.
2. **Port 7547 (CWMP)**: Pintu masuk untuk mesin (Modem/ONU). Jutaan modem dari rumah pelanggan akan mengetuk port ini untuk melapor ke server.
3. **Port 7557 (NBI)**: API internal. Digunakan oleh dsBilling untuk mengobrol dengan GenieACS. **JANGAN diekspos ke publik** demi keamanan.
4. **Port 7567 (FS)**: *File Server* internal untuk menyimpan *firmware* modem.

---

## 2. Arsitektur Domain (Best Practice)
Sangat disarankan untuk memisahkan domain antara Web UI dan CWMP agar trafik manusia dan mesin tidak saling menabrak.

* **`acs.domainanda.com`** -> Diarahkan ke Port 3000 (Untuk Teknisi & Admin).
* **`cwmp.domainanda.com`** -> Diarahkan ke Port 7547 (Ditanamkan ke dalam modem pelanggan).

---

## 3. Konfigurasi Nginx (Reverse Proxy)

Jika Anda menggunakan VPS Nginx sebagai gerbang depan (Proxy) yang meneruskan trafik ke IP lokal (contoh: `192.168.150.15`), berikut adalah konfigurasi `sites-available` yang standar:

### A. Konfigurasi Nginx untuk Web UI (Port 3000)
Buat file `sudo nano /etc/nginx/sites-available/acs`
```nginx
server {
    listen 80;
    server_name acs.domainanda.com;

    location / {
        proxy_pass http://192.168.150.15:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Penting untuk WebSocket GenieACS UI
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

### B. Konfigurasi Nginx untuk CWMP Modem (Port 7547)
Buat file `sudo nano /etc/nginx/sites-available/cwmp`
```nginx
server {
    listen 80;
    server_name cwmp.domainanda.com;

    # Hilangkan batasan ukuran body karena laporan modem bisa sangat besar
    client_max_body_size 0;

    location / {
        proxy_pass http://192.168.150.15:7547;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

### C. Mengaktifkan Nginx
```bash
sudo ln -s /etc/nginx/sites-available/acs /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/cwmp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 4. Konfigurasi Port Forwarding (Mikrotik NAT)
Jika server GenieACS Anda berada murni di belakang Mikrotik tanpa Nginx proxy, Anda harus melakukan *port forwarding* dari IP Publik langsung ke server lokal.

Buka terminal Mikrotik Anda dan ketikkan perintah berikut:

```routeros
# Membuka Akses Web UI (3000) dari Luar
/ip firewall nat
add action=dst-nat chain=dstnat dst-port=3000 in-interface=ether1-WAN protocol=tcp to-addresses=192.168.150.15 to-ports=3000

# Membuka Akses CWMP (7547) agar Modem bisa melapor
/ip firewall nat
add action=dst-nat chain=dstnat dst-port=7547 in-interface=ether1-WAN protocol=tcp to-addresses=192.168.150.15 to-ports=7547
```
*(Ganti `ether1-WAN` dengan antarmuka internet Mikrotik Anda).*

---

## 5. Menanamkan URL ke Modem (CPE)

Setelah semua jaringan siap, Anda tinggal memasukkan URL CWMP ke dalam modem pelanggan:

1. Buka Web UI Modem (`192.168.1.1`).
2. Masuk ke **Network -> TR-069**.
3. Isi **ACS URL** dengan: `http://cwmp.domainanda.com` (jika pakai Nginx) ATAU `http://IP_PUBLIK_ANDA:7547` (jika pakai Mikrotik NAT murni).
4. Klik *Apply/Save*.

Modem akan langsung muncul di halaman web dsBilling dan GenieACS UI Anda!
