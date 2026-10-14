#!/usr/bin/env bash
# ==============================================================================
# dsBilling - Auto Installer & Configurator FreeRADIUS (REST API Mode)
# ==============================================================================

set -e

# Warna Terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}===================================================================${NC}"
echo -e "${BLUE}     dsBilling - Auto Setup FreeRADIUS (REST API & Mikrotik)       ${NC}"
echo -e "${BLUE}===================================================================${NC}"

# 1. Pastikan dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}[ERROR] Skrip ini harus dijalankan sebagai root (gunakan sudo atau root shell)!${NC}"
  exit 1
fi

PROJECT_DIR="/var/www/dsbilling"
if [ ! -d "$PROJECT_DIR" ]; then
  PROJECT_DIR="$(pwd)"
fi

echo -e "\n${YELLOW}[1/6] Menginstal paket FreeRADIUS...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq freeradius freeradius-rest freeradius-utils curl jq

echo -e "${GREEN}✓ Paket FreeRADIUS berhasil diinstal.${NC}"

# 2. Ambil Shared Secret dari Database dsBilling
echo -e "\n${YELLOW}[2/6] Membaca RADIUS Secret dari sistem dsBilling...${NC}"
SECRET=""
if [ -f "$PROJECT_DIR/artisan" ]; then
  SECRET=$(php "$PROJECT_DIR/artisan" tinker --execute="echo \App\Models\ISP\Router::first()?->radius_secret;" 2>/dev/null | tr -d '\r\n')
fi

if [ -z "$SECRET" ] || [ "$SECRET" = "null" ]; then
  SECRET="radius_secret_123"
  echo -e "${YELLOW}  Tidak menemukan router di DB, menggunakan secret default: ${GREEN}${SECRET}${NC}"
else
  echo -e "${GREEN}  ✓ Secret router ditemukan dari database: ${SECRET}${NC}"
fi

# 3. Konfigurasi Modul REST (/etc/freeradius/3.0/mods-available/rest)
echo -e "\n${YELLOW}[3/6] Mengonfigurasi Modul REST FreeRADIUS...${NC}"
cat << 'EOF' > /etc/freeradius/3.0/mods-available/rest
rest {
    connect_uri = "http://127.0.0.1/api/v1/radius"

    authenticate {
        uri = "${..connect_uri}/preauth"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "password": "%{User-Password}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}", "signature": "%{md5:%{User-Name}%{Client-Secret}}"}'
    }

    authorize {
        uri = "${..connect_uri}/authorize"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}", "signature": "%{md5:%{User-Name}%{Client-Secret}}"}'
    }

    accounting {
        uri = "${..connect_uri}/accounting"
        method = 'post'
        body = 'json'
        data = '{"username": "%{User-Name}", "status_type": "%{Acct-Status-Type}", "session_id": "%{Acct-Session-Id}", "input_octets": "%{Acct-Input-Octets}", "output_octets": "%{Acct-Output-Octets}", "nas_ip_address": "%{NAS-IP-Address}", "nas_name": "%{NAS-Identifier}", "signature": "%{md5:%{User-Name}%{Client-Secret}}"}'
    }
}
EOF

# Buat symlink ke mods-enabled jika belum ada
mkdir -p /etc/freeradius/3.0/mods-enabled
ln -sf /etc/freeradius/3.0/mods-available/rest /etc/freeradius/3.0/mods-enabled/rest
echo -e "${GREEN}✓ Modul REST berhasil dikonfigurasi & diaktifkan.${NC}"

# 4. Tambahkan REST ke sites-enabled/default
echo -e "\n${YELLOW}[4/6] Menghubungkan REST ke default virtual server...${NC}"
SITE_DEFAULT="/etc/freeradius/3.0/sites-available/default"

# Backup default site
if [ ! -f "${SITE_DEFAULT}.dsb_backup" ]; then
  cp "$SITE_DEFAULT" "${SITE_DEFAULT}.dsb_backup"
fi

# Sisipkan rest di blok authorize jika belum ada
if ! grep -q "^\s*rest\b" "$SITE_DEFAULT"; then
  # Tambahkan rest ke authorize
  sed -i '/authorize {/a \\trest' "$SITE_DEFAULT"
  # Tambahkan rest ke accounting
  sed -i '/accounting {/a \\trest' "$SITE_DEFAULT"
fi

ln -sf /etc/freeradius/3.0/sites-available/default /etc/freeradius/3.0/sites-enabled/default
echo -e "${GREEN}✓ Virtual site default berhasil diperbarui.${NC}"

# 5. Daftarkan Client MikroTik di clients.conf
echo -e "\n${YELLOW}[5/6] Mendaftarkan Client MikroTik di clients.conf...${NC}"
CLIENTS_CONF="/etc/freeradius/3.0/clients.conf"

if [ ! -f "${CLIENTS_CONF}.dsb_backup" ]; then
  cp "$CLIENTS_CONF" "${CLIENTS_CONF}.dsb_backup"
fi

# Hapus entry all_mikrotik lama jika ada
sed -i '/# BEGIN DSBILLING MIKROTIK/,/# END DSBILLING MIKROTIK/d' "$CLIENTS_CONF"

# Tambahkan entry baru
cat << EOF >> "$CLIENTS_CONF"
# BEGIN DSBILLING MIKROTIK
client all_mikrotik {
    ipaddr = 0.0.0.0/0
    secret = $SECRET
    shortname = mikrotik
    nas_type = other
}
# END DSBILLING MIKROTIK
EOF
echo -e "${GREEN}✓ Client MikroTik didaftarkan dengan secret: ${SECRET}${NC}"

# 6. Atur Firewall & Jalankan Service
echo -e "\n${YELLOW}[6/6] Membuka port firewall & me-restart FreeRADIUS...${NC}"
if command -v ufw >/dev/null 2>&1; then
  ufw allow 1812/udp >/dev/null 2>&1 || true
  ufw allow 1813/udp >/dev/null 2>&1 || true
  ufw allow 3799/udp >/dev/null 2>&1 || true
fi

chown -R freerad:freerad /etc/freeradius/3.0/ 2>/dev/null || true
systemctl daemon-reload
systemctl enable freeradius
systemctl restart freeradius

sleep 2

if systemctl is-active --quiet freeradius; then
  echo -e "\n${GREEN}===================================================================${NC}"
  echo -e "${GREEN}🎉 SUKSES! FreeRADIUS berhasil diinstal dan berjalan normal!      ${NC}"
  echo -e "${GREEN}===================================================================${NC}"
  echo -e "Port Aktif      : UDP 1812 (Auth), UDP 1813 (Acct)"
  echo -e "Client Secret   : ${YELLOW}${SECRET}${NC}"
  echo -e "Status Service  : ${GREEN}ACTIVE (RUNNING)${NC}"
  echo -e "\nLangkah selanjutnya di Winbox MikroTik:"
  echo -e "1. Buka menu ${BLUE}Radius${NC} di Winbox."
  echo -e "2. Pastikan ${YELLOW}Secret${NC} diisi: ${GREEN}${SECRET}${NC}"
  echo -e "3. Klik ${BLUE}Reset Status${NC}."
  echo -e "4. Coba koneksikan pelanggan PPPoE / Hotspot."
else
  echo -e "\n${RED}[PERINGATAN] FreeRADIUS gagal start. Jalankan 'freeradius -X' untuk melihat pesan error.${NC}"
fi
