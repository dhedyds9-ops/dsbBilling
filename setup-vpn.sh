#!/bin/bash
# =========================================================================
# dsBilling VPN Server Auto Installer (Ubuntu/Debian)
# Menghubungkan MikroTik tanpa IP Publik ke dsBilling VPS (seperti MSRadius)
# =========================================================================

if [ "$EUID" -ne 0 ]; then
  echo "[-] Gagal: Silakan jalankan script ini sebagai root (Gunakan: sudo bash setup-vpn.sh)"
  exit 1
fi

echo "=========================================================="
echo "      dsBilling VPN Server Installer untuk MikroTik      "
echo "=========================================================="
echo ""

# 1. Update paket & instal dependensi
echo "[1/4] Menginstal paket VPN & Jaringan..."
apt-get update -y
apt-get install -y ppp iptables curl ufw

# Deteksi IP Publik Server
SERVER_IP=$(curl -4 -s ifconfig.me || curl -4 -s icanhazip.com || hostname -I | awk '{print $1}')
echo "[*] IP Publik Server Terdeteksi: $SERVER_IP"

# 2. Instal pptpd / sstp-server / openvpn ringan untuk MikroTik
echo "[2/4] Menginstal PPTP / SSTP VPN Server..."
apt-get install -y pptpd

# Konfigurasi Subnet VPN dsBilling (Subnet 10.200.0.0/24)
cat << 'EOF' > /etc/pptpd.conf
option /etc/ppp/pptpd-options
logwtmp
localip 10.200.0.1
remoteip 10.200.0.10-250
EOF

cat << 'EOF' > /etc/ppp/pptpd-options
name pptpd
refuse-pap
refuse-chap
refuse-mschap
require-mschap-v2
require-mppe-128
ms-dns 8.8.8.8
ms-dns 1.1.1.1
proxyarp
nodefaultroute
lock
nobsdcomp
novj
novjccomp
nologfd
EOF

# 3. Aktifkan IP Forwarding & Firewall
echo "[3/4] Mengaktifkan IP Forwarding & Rule Jaringan..."
sysctl -w net.ipv4.ip_forward=1
sed -i -e '$a\net.ipv4.ip_forward=1' /etc/sysctl.conf

# Buka Port VPN & izinkan traffic subnet VPN
iptables -A INPUT -p tcp --dport 1723 -j ACCEPT
iptables -A INPUT -p gre -j ACCEPT
iptables -A INPUT -s 10.200.0.0/24 -j ACCEPT
iptables -A FORWARD -s 10.200.0.0/24 -j ACCEPT
iptables -A FORWARD -d 10.200.0.0/24 -j ACCEPT
iptables -t nat -A POSTROUTING -s 10.200.0.0/24 -j MASQUERADE

# Simpan iptables agar permanen saat reboot
apt-get install -y iptables-persistent
netfilter-persistent save

# Restart Service VPN
systemctl daemon-reload
systemctl enable pptpd
systemctl restart pptpd

echo "[4/4] Selesai!"
echo ""
echo "=========================================================="
echo "          VPN Server dsBilling Berhasil Aktif!            "
echo "=========================================================="
echo "IP Gateway VPN (Server dsBilling) : 10.200.0.1"
echo "Range IP Router MikroTik          : 10.200.0.10 - 10.200.0.250"
echo ""
echo "Untuk menambahkan akun router baru di VPS, cukup jalankan:"
echo "echo \"username * password *\" >> /etc/ppp/chap-secrets"
echo "=========================================================="
