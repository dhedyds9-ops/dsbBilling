<?php

// 1. DOCKERFILE
@mkdir('docker', 0755, true);
$dockerfile = <<<EOF
FROM php:8.2-fpm

# Update OS and install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    snmp \
    libsnmp-dev \
    snmp-mibs-downloader \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd snmp

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
EOF;
file_put_contents('docker/Dockerfile', $dockerfile);

// 2. NGINX CONF
$nginxConf = <<<EOF
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/html/public;

    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
        gzip_static on;
    }
}
EOF;
file_put_contents('docker/nginx.conf', $nginxConf);

// 3. DOCKER COMPOSE
$dockerCompose = <<<EOF
version: '3.8'

services:
  # ==========================================
  # dsBilling Services (App, Web, DB, Redis)
  # ==========================================
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    restart: unless-stopped
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
      - redis
    networks:
      - isp-network

  webserver:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - isp-network

  db:
    image: mariadb:10.11
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: \${DB_DATABASE:-dsbilling}
      MYSQL_USER: \${DB_USERNAME:-dsbilling}
      MYSQL_PASSWORD: \${DB_PASSWORD:-secret}
      MYSQL_ROOT_PASSWORD: \${DB_PASSWORD:-secret}
    volumes:
      - db-data:/var/lib/mysql
    networks:
      - isp-network

  redis:
    image: redis:alpine
    restart: unless-stopped
    networks:
      - isp-network

  # ==========================================
  # GenieACS Services (TR-069 ACS)
  # ==========================================
  mongo:
    image: mongo:6
    restart: unless-stopped
    volumes:
      - mongo-data:/data/db
    networks:
      - isp-network

  genieacs-cwmp:
    image: genieacs/genieacs:1.2.13
    command: ["genieacs-cwmp"]
    restart: unless-stopped
    ports:
      - "7547:7547"
    environment:
      GENIEACS_MONGODB_CONNECTION_URL: "mongodb://mongo:27017/genieacs"
    depends_on:
      - mongo
    networks:
      - isp-network

  genieacs-nbi:
    image: genieacs/genieacs:1.2.13
    command: ["genieacs-nbi"]
    restart: unless-stopped
    ports:
      - "7557:7557"
    environment:
      GENIEACS_MONGODB_CONNECTION_URL: "mongodb://mongo:27017/genieacs"
    depends_on:
      - mongo
    networks:
      - isp-network

  genieacs-fs:
    image: genieacs/genieacs:1.2.13
    command: ["genieacs-fs"]
    restart: unless-stopped
    ports:
      - "7567:7567"
    environment:
      GENIEACS_MONGODB_CONNECTION_URL: "mongodb://mongo:27017/genieacs"
    depends_on:
      - mongo
    networks:
      - isp-network

  genieacs-ui:
    image: genieacs/genieacs:1.2.13
    command: ["genieacs-ui"]
    restart: unless-stopped
    ports:
      - "3000:3000"
    environment:
      GENIEACS_MONGODB_CONNECTION_URL: "mongodb://mongo:27017/genieacs"
    depends_on:
      - mongo
    networks:
      - isp-network

networks:
  isp-network:
    driver: bridge

volumes:
  db-data:
  mongo-data:
EOF;
file_put_contents('docker-compose.yml', $dockerCompose);

// 4. INSTALL.SH
$installSh = <<<EOF
#!/bin/bash
# =========================================================================
# dsBilling + GenieACS Auto Installer (Ubuntu/Debian)
# =========================================================================

if [ "\$EUID" -ne 0 ]; then
  echo "[-] Gagal: Silakan jalankan script ini sebagai root (Gunakan sudo)"
  exit 1
fi

echo "[1/6] Memperbarui Sistem & Menyiapkan Repositori..."
apt-get update -y && apt-get upgrade -y
apt-get install -y software-properties-common curl zip unzip git ca-certificates gnupg

# Repositori PHP
add-apt-repository ppa:ondrej/php -y

# Repositori Node.js (untuk GenieACS)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

# Repositori MongoDB (untuk GenieACS)
curl -fsSL https://pgp.mongodb.com/server-6.0.asc | gpg -o /usr/share/keyrings/mongodb-server-6.0.gpg --dearmor
echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-6.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/6.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list

apt-get update -y

echo "[2/6] Menginstal Nginx, PHP 8.2, MariaDB, Redis, Node.js, & MongoDB..."
apt-get install -y nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-snmp snmp snmp-mibs-downloader redis-server mariadb-server nodejs mongodb-org

systemctl enable mariadb redis-server nginx php8.2-fpm mongod
systemctl start mariadb redis-server nginx php8.2-fpm mongod

echo "[3/6] Menginstal dan Mengonfigurasi GenieACS..."
npm install -g genieacs@1.2.13

useradd --system --no-create-home --user-group genieacs || true
mkdir -p /opt/genieacs/ext
chown genieacs:genieacs /opt/genieacs/ext

# Setup Systemd Services untuk GenieACS
for srv in cwmp nbi fs ui; do
cat <<SYS > /etc/systemd/system/genieacs-\$srv.service
[Unit]
Description=GenieACS \$srv
After=network.target

[Service]
User=genieacs
EnvironmentFile=/opt/genieacs/genieacs.env
ExecStart=/usr/bin/genieacs-\$srv

[Install]
WantedBy=default.target
SYS
done

echo "GENIEACS_CWMP_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-cwmp-access.log" > /opt/genieacs/genieacs.env
echo "GENIEACS_NBI_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-nbi-access.log" >> /opt/genieacs/genieacs.env
echo "GENIEACS_FS_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-fs-access.log" >> /opt/genieacs/genieacs.env
echo "GENIEACS_UI_ACCESS_LOG_FILE=/var/log/genieacs/genieacs-ui-access.log" >> /opt/genieacs/genieacs.env
echo "GENIEACS_DEBUG_FILE=/var/log/genieacs/genieacs-debug.yaml" >> /opt/genieacs/genieacs.env

mkdir -p /var/log/genieacs
chown genieacs:genieacs /var/log/genieacs

systemctl daemon-reload
systemctl enable genieacs-cwmp genieacs-nbi genieacs-fs genieacs-ui
systemctl start genieacs-cwmp genieacs-nbi genieacs-fs genieacs-ui

echo "[4/6] Mengonfigurasi dsBilling..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Diasumsikan script berjalan di dalam folder aplikasi dsbilling
if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan storage:link
chown -R www-data:www-data \$PWD
chmod -R 775 storage bootstrap/cache

echo "[5/6] Mengonfigurasi Nginx untuk dsBilling..."
cat <<NGX > /etc/nginx/sites-available/dsbilling
server {
    listen 80;
    server_name _;
    root \$PWD/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGX

ln -s /etc/nginx/sites-available/dsbilling /etc/nginx/sites-enabled/ 2>/dev/null
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

echo "[6/6] Selesai!"
echo "====================================================================="
echo " Instalasi dsBilling + GenieACS berhasil diselesaikan!               "
echo "                                                                     "
echo " 1. Setup Database:                                                  "
echo "    Anda perlu membuat database MariaDB secara manual, lalu          "
echo "    mengisinya di file .env, kemudian jalankan:                      "
echo "    php artisan migrate                                              "
echo "                                                                     "
echo " 2. Akses Aplikasi:                                                  "
echo "    dsBilling : http://<IP_SERVER>                                   "
echo "    GenieACS  : http://<IP_SERVER>:3000                              "
echo "====================================================================="
EOF;
file_put_contents('install.sh', $installSh);
chmod('install.sh', 0755);

echo "All installation files created successfully.\n";
