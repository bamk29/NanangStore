# 7. SOP Maintenance & Update (Wajib Baca)

Dokumen ini berisi **Contekan Perintah** untuk pekerjaan rutin. Simpan atau print halaman ini agar tidak lupa.

## A. Cara Update Aplikasi (Setelah ada perubahan kode)
Setiap kali Anda melakukan update kodingan dan sudah di-push ke GitHub, lakukan langkah ini di STB:

1.  **Masuk ke Folder Project**
    *Wajib dilakukan sebelum menjalankan perintah `php artisan` apapun.*
    ```bash
    cd /var/www/nanangstore
    ```

2.  **Ambil Update Terbaru (Git Pull)**
    ```bash
    sudo git pull origin main
    ```

3.  **Build Ulang Aset (PENTING)**
    Agar tampilan web (CSS/JS) berubah sesuai update baru.
    ```bash
    npm install
    npm run build
    ```

4.  **Perbaiki Izin File (Permission Fix)**
    *Wajib dilakukan agar Samba (Windows) bisa edit file dan Web Server tidak error.*
    ```bash
    sudo chown -R www-data:www-data /var/www/nanangstore
    sudo chmod -R 775 /var/www/nanangstore/storage
    sudo chmod -R 775 /var/www/nanangstore/bootstrap/cache
    
    # Tambahan agar User Samba (root) bisa edit file:
    sudo chmod -R 777 /var/www/nanangstore
    ```

---

## B. Cara Menjalankan Perintah Artisan
Jika ingin menjalankan perintah manual (misal: buat user baru, clear cache, migrate database).

1.  **Pastikan sudah di dalam folder:**
    ```bash
    cd /var/www/nanangstore
    ```

2.  **Contoh Perintah Umum:**
    *   **Clear Cache** (Jika ada error aneh/tampilan nyangkut):
        ```bash
        php artisan optimize:clear
        ```
    *   **Migrate Database** (Jika ada tabel baru):
        ```bash
        php artisan migrate
        ```
    *   **Buat Link Storage** (Jika gambar produk tidak muncul):
        ```bash
        php artisan storage:link
        ```

---

## C. Cara Edit & Update Server Printer
Jika Anda ingin mengubah kodingan printer (misal: ubah format struk).

1.  **Masuk ke Folder Printer:**
    ```bash
    cd /opt/printer-server
    ```

2.  **Edit File `server.js`:**
    Gunakan `nano` text editor.
    ```bash
    sudo nano server.js
    ```
    jika susah editnya Hapus saja dan lakukan langkah ini:
    ```bash
    sudo rm server.js
    ```
    lalu lakukan langkah ini:
    ```bash
    sudo nano server.js
    ```
    paste kode baru.
  
  

3.  **Cara Simpan di Nano:**
    *   Lakukan perubahan kode...
    *   Tekan **Ctrl + X** (untuk keluar).
    *   Tekan **Y** (untuk konfirmasi save).
    *   Tekan **Enter** (untuk simpan nama file).

4.  **Restart Service Printer:**
    *Wajib dilakukan agar perubahan efek.*
    ```bash
    pm2 restart printer-service
    ```
    *Cek status:*
    ```bash
    pm2 status
    ```

---

## D. Jika Server Mati Lampu / Restart
Semua service (Web Server, Database, Printer) seharusnya **otomatis nyala**.
Namun jika ada kendala, cek dengan perintah ini:

1.  **Cek Nginx (Web):** `sudo systemctl status nginx`
2.  **Cek Database:** `sudo systemctl status mariadb`
3.  **Cek Printer:** `pm2 status`

---

## E. Cek Status & Test (Troubleshooting)
Gunakan perintah ini untuk memastikan semuanya berjalan normal.

1.  **Cek Web Server (Nginx & PHP)**
    *   Cek Status Service:
        ```bash
        sudo systemctl status nginx
        ```
    *   Test Akses phpMyAdmin (Cek Header):
        ```bash
        curl -I http://localhost/phpmyadmin/
        ```
        *Harus muncul: `HTTP/1.1 200 OK`.*

2.  **Cek Database (MariaDB)**
    *   Cek Status Service:
        ```bash
        sudo systemctl status mariadb
        ```
    *   Test Login Database:
        ```bash
        mysql -u root -p -e "SHOW DATABASES;"
        ```
        *Masukkan password, lalu akan muncul daftar database.*

3.  **Cek Printer Server**
    *   Cek Status Service:
        ```bash
        pm2 status
        ```
        *Pastikan `printer-service` statusnya `online` (hijau).*
    
    *   **Test Print Manual (PENTING):**
        Kirim perintah cetak langsung dari terminal untuk tes printer.
        ```bash
        curl -X POST http://localhost:8000/print -H "Content-Type: application/json" -d '{"printType": "singleOrder"}'
        ```
        *Printer harusnya mencetak struk kecil bertuliskan "PESANAN".*

---

## F. Manajemen Database (Darurat & Harian)

### 1. Lupa Password Root Database (Reset Password)
Jika Anda benar-benar lupa password database, ikuti langkah ini (Hati-hati!):

1.  **Matikan Service Database:**
    ```bash
    sudo systemctl stop mariadb
    ```
2.  **Jalankan Mode Aman (Tanpa Password):**
    ```bash
    sudo mysqld_safe --skip-grant-tables &
    ```
3.  **Login Tanpa Password:**
    ```bash
    mysql -u root
    ```
4.  **Reset Password (SQL):**
    Ketik perintah ini baris per baris (Ganti `PASSWORD_BARU` dengan password Anda):
    ```sql
    FLUSH PRIVILEGES;
    ALTER USER 'root'@'localhost' IDENTIFIED BY 'PASSWORD_BARU';
    FLUSH PRIVILEGES;
    EXIT;
    ```
5.  **Matikan Mode Aman & Restart:**
    ```bash
    sudo pkill mysqld
    sudo systemctl start mariadb
    ```
    *Sekarang coba login lagi dengan password baru.*

### 2. Membuat Database Baru
Jika Anda ingin membuat database lain (misal untuk aplikasi kedua/backup manual).

1.  **Login MySQL:**
    ```bash
    mysql -u root -p
    ```
2.  **Buat Database:**
    ```sql
    CREATE DATABASE nama_database_baru;
    GRANT ALL PRIVILEGES ON nama_database_baru.* TO 'root'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
    ```

---

## G. Tips Tambahan (Monitoring)
Perintah spele tapi penting untuk menjaga kesehatan STB.

1.  **Cek Sisa Penyimpanan (Disk Space):**
    Jangan sampai penuh (100%), nanti database error.
    ```bash
    df -h
    ```
    *Lihat baris `/dev/root` atau `/`. Jika `Use%` sudah diatas 90%, hapus file backup lama.*

2.  **Cek Penggunaan RAM (Memory):**
    Jika aplikasi lemot, cek apakah RAM penuh.
    ```bash
    free -h
    # atau tampilan grafik
    htop
    ```

3.  **Reboot Aman:**
    Jika STB terasa aneh/berat, restart saja.
    ```bash
    sudo reboot
    ```

---

**Kembali ke Menu Utama:**
🏠 **[Panduan Setup STB](../TaskSTBSetub.md)**
