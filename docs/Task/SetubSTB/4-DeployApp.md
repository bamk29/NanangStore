# 4. Deploy Aplikasi NanangStore

## A. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/bamk29/NanangStore.git nanangstore
cd nanangstore
```

## B. Set Permission
Penting agar Nginx bisa baca/tulis.

```bash
sudo chown -R www-data:www-data /var/www/nanangstore
sudo chmod -R 775 /var/www/nanangstore/storage
sudo chmod -R 775 /var/www/nanangstore/bootstrap/cache
```

## C. Install Dependencies

```bash
# Install PHP libs
composer install --optimize-autoloader --no-dev

# Install JS libs & Build
npm install
npm run build
```

## D. Setup Database & Environment

1.  **Buat Database**
    ```bash
    sudo mysql -u root -p
    CREATE DATABASE nanangstore;
    EXIT;
    ```

2.  **Setup .env**
    ```bash
    cp .env.example .env
    nano .env
    ```
    Sesuaikan:
    ```env
    APP_URL=http://[IP_STB_ANDA]
    DB_DATABASE=nanangstore
    DB_USERNAME=root
    DB_PASSWORD=[PASSWORD_ANDA]
    ```

3.  **Generate Key & Migrate**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    php artisan storage:link
    ```

## E. Selesai!
Akses web via browser: `http://[IP_STB_ANDA]`
Akses phpMyAdmin: `http://[IP_STB_ANDA]/phpmyadmin`

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 5: Maintenance & Backup](5-SambaAndBackup.md)**
