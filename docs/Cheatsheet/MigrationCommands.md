# Artisan Migration Cheatsheet

Catatan cara menjalankan command artisan migration untuk file spesifik (jalur path langsung).

## 1. Menjalankan Migration Spesifik (Migrate Up)
Untuk menjalankan satu file migration saja tanpa menjalankan yang lain.

```bash
php artisan migrate --path=/database/migrations/nama_file_migration.php
```

**Contoh:**
```bash
php artisan migrate --path=/database/migrations/2025_12_03_203000_add_cost_price_to_transaction_details.php
```

> **Catatan:** Path harus relatif dari root project (folder di mana Anda menjalankan command).

## 2. Rollback Migration Spesifik (Migrate Down)
Untuk membatalkan (rollback) satu file migration spesifik.

```bash
php artisan migrate:rollback --path=/database/migrations/nama_file_migration.php
```

**Contoh:**
```bash
php artisan migrate:rollback --path=/database/migrations/2025_12_03_203000_add_cost_price_to_transaction_details.php
```

## 3. Refresh Migration Spesifik
Untuk rollback lalu migrate ulang satu file saja (berguna saat development).

```bash
php artisan migrate:refresh --path=/database/migrations/nama_file_migration.php
```

## 4. Cek Status Migration
Untuk melihat status migration mana yang sudah jalan atau belum.

```bash
php artisan migrate:status
```
