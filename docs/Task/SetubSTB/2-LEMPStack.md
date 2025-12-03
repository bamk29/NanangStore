# 2. Install LEMP Stack (Linux, Nginx, MySQL, PHP)

## A. Install Nginx (Web Server)

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

## B. Install MariaDB (Database)

```bash
sudo apt install -y mariadb-server
sudo mysql_secure_installation
```
*   Ikuti wizard: Set root password? **Y**, Remove anonymous users? **Y**, Disallow root login remotely? **Y**, Remove test database? **Y**, Reload privilege tables? **Y**.

## C. Install PHP 8.3 (FPM)
Armbian default mungkin belum ada PHP 8.3, tambahkan PPA ondrej/php.

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-common php8.3-curl php8.3-xml php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-intl
```

**Konfigurasi PHP FPM:**
Edit file `php.ini` jika perlu (misal upload_max_filesize).
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Restart PHP FPM:
```bash
sudo systemctl restart php8.3-fpm
```

## D. Install phpMyAdmin (Optional - via Composer/Manual)
Cara paling aman di server custom adalah manual download agar tidak konflik dependency.

```bash
cd /var/www/html
sudo wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip
sudo unzip phpMyAdmin-latest-all-languages.zip
sudo mv phpMyAdmin-*-all-languages phpmyadmin
sudo rm phpMyAdmin-latest-all-languages.zip
```

**Konfigurasi Nginx untuk phpMyAdmin & Laravel:**
Buat file config baru:
```bash
sudo nano /etc/nginx/sites-available/nanangstore
```

Isi (Sesuaikan path):
```nginx
# Server Block 1: Aplikasi NanangStore (Port 80)
server {
    listen 80;
    server_name localhost;
    root /var/www/nanangstore/public;

    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}

# Server Block 2: phpMyAdmin (Port 8081)
server {
    listen 8081;
    server_name localhost;
    root /var/www/html/phpmyadmin; # Pastikan path ini benar sesuai install manual

    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

**Cara Simpan:** Tekan `Ctrl+X`, lalu `Y`, lalu `Enter`.

Aktifkan config:
```bash
sudo ln -s /etc/nginx/sites-available/nanangstore /etc/nginx/sites-enabled/
sudo unlink /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 3: Setup Printer Server](3-NodePrinter.md)**
