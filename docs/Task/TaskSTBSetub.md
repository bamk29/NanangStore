# Panduan Setup Server STB HG680P (Armbian)

Dokumen ini berisi panduan langkah demi langkah untuk mengubah STB HG680P menjadi Web Server & Printer Server untuk aplikasi NanangStore.

## Daftar Isi Langkah-Langkah

Silakan ikuti panduan di bawah ini secara berurutan:

1.  **[Persiapan Sistem](SetubSTB/1-SystemPrep.md)**
    *   Update OS, Install Tools Dasar, Set Timezone.
2.  **[Install LEMP Stack](SetubSTB/2-LEMPStack.md)**
    *   Nginx, MariaDB, PHP 8.3, phpMyAdmin.
3.  **[Setup Printer Server](SetubSTB/3-NodePrinter.md)**
    *   Node.js, Express.js, Driver Printer Thermal (USB).
4.  **[Deploy Aplikasi](SetubSTB/4-DeployApp.md)**
    *   Clone Git, Composer, NPM Build, Database Migration.
5.  **[Maintenance & Backup](SetubSTB/5-SambaAndBackup.md)**
    *   Samba File Sharing, Auto Backup Database (Hourly/Daily/Weekly).
6.  **[Akses Remote](SetubSTB/6-RemoteAccess.md)**
    *   SSH via Tailscale (Aman & Mudah), Web via Cloudflare Tunnel.
7.  **[SOP Maintenance](SetubSTB/7-MaintenanceSOP.md)**
    *   Cara Update App, Fix Permission, Edit Printer, Restart Service.

---

## Ringkasan Perintah Cepat (Cheatsheet)

Jika Anda sudah paham alurnya, berikut ringkasan perintah intinya:

```bash
# 1. System & Tools
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl wget zip unzip nginx mariadb-server
# Dependencies untuk Canvas (Printer QR):
sudo apt install -y build-essential libcairo2-dev libpango1.0-dev libjpeg-dev libgif-dev librsvg2-dev

# 2. PHP 8.3 & Composer
sudo add-apt-repository ppa:ondrej/php
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-common php8.3-curl php8.3-xml php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-intl
# Install Composer:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 3. Node.js & PM2 (Gunakan Versi Terbaru/LTS)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2

# 4. Deploy
cd /var/www
sudo git clone https://github.com/bamk29/NanangStore.git
cd NanangStore
composer install --no-dev
npm install && npm run build
cp .env.example .env
php artisan migrate --seed
```
