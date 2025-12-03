# 5. Install Samba & Auto Backup Database

Fitur tambahan untuk kemudahan transfer file dan keamanan data.

## A. Install Samba (File Sharing)
Agar folder server bisa dibuka dari Windows (Network Place).

1.  **Install Samba:**
    ```bash
    sudo apt install -y samba
    ```

2.  **Buat User Samba:**
    Misal user linux Anda adalah `server` (atau `root` jika pakai root). Kita buat password untuk akses sambanya.
    ```bash
    sudo smbpasswd -a root
    # Masukkan password baru untuk akses file sharing
    ```

3.  **Konfigurasi Folder Share:**
    Edit file config:
    ```bash
    sudo nano /etc/samba/smb.conf
    ```
    Tambahkan di paling bawah (untuk share folder project):
    ```ini
    [NanangStoreApp]
    path = /var/www/nanangstore
    browseable = yes
    read only = no
    guest ok = no
    create mask = 0775
    directory mask = 0775
    force user = www-data
    valid users = root
    ```
    *Note: `force user = www-data` agar file yang diedit tetap milik web server.*

4.  **Restart Samba:**
    ```bash
    sudo systemctl restart smbd
    ```
    Sekarang coba akses dari Windows: `\\IP_STB\NanangStoreApp`.

---

## B. Auto Backup Database (Hourly, Daily, Weekly)
Skrip ini akan membackup database secara otomatis dan menghapus file lama agar memori tidak penuh.

### 1. Siapkan Folder Backup
```bash
mkdir -p /root/backups
```

### 2. Buat Script Backup
Kita buat script pintar yang bisa menangani backup per jam, hari, dan minggu.

```bash
sudo nano /usr/local/bin/db_backup.sh
```

**Isi Script (Copy-Paste semua):**
```bash
#!/bin/bash

# --- KONFIGURASI ---
DB_USER="root"
DB_PASS="password_mysql_anda" # Ganti dengan password database
DB_NAME="nanangstore"
BACKUP_BASE="/root/backups"
DATE=$(date +"%Y-%m-%d_%H-%M-%S")

# Argumen input: hourly, daily, atau weekly
TYPE=$1

if [ -z "$TYPE" ]; then
    echo "Usage: $0 {hourly|daily|weekly}"
    exit 1
fi

# Buat folder jika belum ada
TARGET_DIR="$BACKUP_BASE/$TYPE"
mkdir -p "$TARGET_DIR"

# Lakukan Backup (Compressed)
FILENAME="$TARGET_DIR/$DB_NAME-$TYPE-$DATE.sql.gz"
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > "$FILENAME"

echo "Backup $TYPE created: $FILENAME"

# --- AUTO DELETE (RETENTION POLICY) ---
# Hapus file lama agar disk tidak penuh

if [ "$TYPE" == "hourly" ]; then
    # Simpan 24 jam terakhir (hapus yang lebih tua dari 1440 menit)
    find "$TARGET_DIR" -type f -name "*.sql.gz" -mmin +1440 -delete
    echo "Cleaned up hourly backups older than 24 hours."

elif [ "$TYPE" == "daily" ]; then
    # Simpan 7 hari terakhir
    find "$TARGET_DIR" -type f -name "*.sql.gz" -mtime +7 -delete
    echo "Cleaned up daily backups older than 7 days."

elif [ "$TYPE" == "weekly" ]; then
    # Simpan 4 minggu terakhir (1 bulan)
    find "$TARGET_DIR" -type f -name "*.sql.gz" -mtime +28 -delete
    echo "Cleaned up weekly backups older than 4 weeks."
fi
```

### 3. Beri Izin Eksekusi
```bash
sudo chmod +x /usr/local/bin/db_backup.sh
```

### 4. Pasang di Cronjob (Penjadwalan Otomatis)
Buka crontab:
```bash
sudo crontab -e
```

Tambahkan baris berikut di paling bawah:

```bash
# Backup Per Jam (Setiap menit ke-0) -> Simpan 24 file terakhir
0 * * * * /usr/local/bin/db_backup.sh hourly

# Backup Per Hari (Jam 00:00) -> Simpan 7 file terakhir
0 0 * * * /usr/local/bin/db_backup.sh daily

# Backup Per Minggu (Hari Minggu Jam 00:00) -> Simpan 4 file terakhir
0 0 * * 0 /usr/local/bin/db_backup.sh weekly
```

Simpan dan keluar (biasanya `Ctrl+X`, `Y`, `Enter`).

### Selesai!
Sekarang database Anda aman:
*   Jika error baru saja terjadi -> Restore dari folder `hourly`.
*   Jika butuh data kemarin -> Restore dari folder `daily`.
*   Storage aman karena file lama otomatis dihapus.

---

## C. Cara Restore Database
Jika terjadi masalah dan Anda perlu mengembalikan data dari backup.

1.  **Cari File Backup:**
    Lihat daftar backup yang tersedia:
    ```bash
    ls -lh /root/backups/hourly
    # atau
    ls -lh /root/backups/daily
    ```

2.  **Restore Database:**
    Gunakan perintah berikut (ganti `NAMA_FILE.sql.gz` dengan nama file backup yang Anda pilih):
    ```bash
    # 1. Ekstrak dan Import langsung
    zcat /root/backups/hourly/NAMA_FILE.sql.gz | sudo mysql -u root -p nanangstore
    ```
    *Masukkan password database saat diminta.*

3.  **Selesai!**
    Database sudah kembali ke kondisi saat backup tersebut dibuat.

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 6: Akses Remote (Tailscale)](6-RemoteAccess.md)**
