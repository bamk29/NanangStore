# Panduan Manual Migrasi Index Database

Jika migration `2025_12_03_144500_add_indexes_for_performance.php` gagal dijalankan, Anda bisa menambahkan index secara manual menggunakan salah satu cara di bawah ini.

## Opsi 1: Menggunakan Artisan Tinker (Recommended)

Jalankan perintah berikut di terminal untuk masuk ke mode tinker:

```bash
php artisan tinker
```

Lalu copy-paste perintah berikut satu per satu:

```php
// Index untuk tabel transactions
try { DB::statement('CREATE INDEX transactions_customer_id_index ON transactions (customer_id)'); } catch(\Exception $e) { echo "Index customer_id already exists\n"; }
try { DB::statement('CREATE INDEX transactions_created_at_index ON transactions (created_at)'); } catch(\Exception $e) { echo "Index created_at already exists\n"; }
try { DB::statement('CREATE INDEX transactions_status_index ON transactions (status)'); } catch(\Exception $e) { echo "Index status already exists\n"; }

// Index untuk tabel transaction_details
try { DB::statement('CREATE INDEX transaction_details_product_id_index ON transaction_details (product_id)'); } catch(\Exception $e) { echo "Index product_id already exists\n"; }
try { DB::statement('CREATE INDEX transaction_details_transaction_id_index ON transaction_details (transaction_id)'); } catch(\Exception $e) { echo "Index transaction_id already exists\n"; }

// Index untuk tabel product_usages
try { DB::statement('CREATE INDEX product_usages_usage_count_index ON product_usages (usage_count)'); } catch(\Exception $e) { echo "Index usage_count already exists\n"; }
```

Ketik `exit` untuk keluar dari tinker.

---

## Opsi 2: Menggunakan SQL Client (phpMyAdmin / DBeaver / HeidiSQL)

Jika Anda memiliki akses langsung ke database, jalankan query SQL berikut:

```sql
-- Tabel transactions
CREATE INDEX transactions_customer_id_index ON transactions (customer_id);
CREATE INDEX transactions_created_at_index ON transactions (created_at);
CREATE INDEX transactions_status_index ON transactions (status);

-- Tabel transaction_details
CREATE INDEX transaction_details_product_id_index ON transaction_details (product_id);
CREATE INDEX transaction_details_transaction_id_index ON transaction_details (transaction_id);

-- Tabel product_usages
CREATE INDEX product_usages_usage_count_index ON product_usages (usage_count);
```

---

## Catatan Penting
*   Perintah di atas menggunakan `CREATE INDEX` standar. Jika nama index sudah ada, database akan menolak perintah tersebut (aman, tidak merusak data).
*   Index ini bertujuan untuk mempercepat query pencarian dan pelaporan.
