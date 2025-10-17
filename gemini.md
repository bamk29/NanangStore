# Laravel Livewire POS System

Dokumentasi ini menjelaskan arsitektur, fitur, dan tujuan dari aplikasi Point of Sale (POS) yang dibangun menggunakan Laravel Livewire.

⚙️ **Framework & Stack**

*   **Backend**: Laravel 12
*   **Frontend**: Livewire 3, Alpine.js (v3+)
*   **Styling**: TailwindCSS
*   **Database**: MySQL/MariaDB
*   **Asset Bundling**: Vite

🎯 **Tujuan Aplikasi**

Menyediakan sistem Point of Sale (POS) yang cepat, ringan, dan kaya fitur untuk usaha kecil hingga menengah seperti toko kelontong, warung, butcher (toko daging), atau mini grosir. Aplikasi ini dirancang untuk mengelola seluruh alur bisnis mulai dari pembelian hingga penjualan dan pelaporan.

---

📊 **Fitur Utama**

Aplikasi ini memiliki banyak fitur canggih yang terintegrasi untuk mengelola operasional toko secara efisien.

✅ **1. Kasir / Point of Sale (POS)**
Antarmuka kasir modern yang dirancang untuk kecepatan dan kemudahan penggunaan.
*   **Pencarian & Penyortiran Produk**: Pencarian cepat berdasarkan nama/kode, dan produk diurutkan berdasarkan popularitas (paling sering terjual).
*   **Dukungan Barcode Scanner**: Mempercepat input produk dengan pemindai barcode.
*   **Harga Dinamis**: Sistem otomatis memilih antara harga eceran dan grosir berdasarkan jumlah item di keranjang.
*   **Manajemen Pelanggan Terintegrasi**:
    *   Cari atau tambah pelanggan baru langsung dari antarmuka kasir.
    *   Lihat sisa hutang pelanggan saat dipilih.
*   **Sistem Pembayaran Fleksibel**:
    *   Mendukung metode **Cash, Transfer, dan Hutang**.
    *   **Manajemen Hutang Canggih**: Jika pembayaran kurang, sistem otomatis menambahkan sisa tagihan ke saldo hutang pelanggan.
    *   Opsi untuk menggabungkan pembayaran hutang lama dengan transaksi baru.
*   **Transaksi Tertunda (Hold Transaction)**: Simpan transaksi saat ini untuk melayani pelanggan lain, dan lanjutkan lagi nanti dari daftar transaksi tunda.
*   **Sistem Poin Loyalitas**: Pelanggan mendapatkan poin untuk setiap transaksi (yang tidak menambah hutang), yang dapat ditukarkan nanti.

✅ **2. Manajemen Inventaris & Pembelian**
Fitur lengkap untuk mengelola alur masuk dan keluar barang.
*   **Manajemen Produk**: CRUD untuk produk dengan detail lengkap, termasuk harga jual, harga acuan supplier, dan harga modal terakhir.
*   **Manajemen Stok Boks & Satuan**: Input stok utama adalah satuan dasar, dan stok dalam boks akan terhitung otomatis. Saat penjualan, stok boks juga ikut diperbarui.
*   **Manajemen Kategori & Unit**: Kelola kategori dan satuan produk (Pcs, Box, Pack, dll).
*   **Manajemen Supplier**: Database untuk semua pemasok barang.
*   **Sistem Purchase Order (PO) Fleksibel**:
    *   Buat pesanan pembelian ke supplier dengan mudah. Harga barang otomatis terisi dari harga acuan supplier yang tersimpan di data produk.
    *   Form pemesanan yang fleksibel, memungkinkan input per boks atau per satuan dengan kalkulasi harga otomatis.
    *   **Penerimaan Stok Otomatis**: Saat barang dari PO diterima, **stok satuan dan stok boks** akan bertambah secara otomatis. **Harga modal (cost_price) produk juga diperbarui**, memastikan perhitungan laba yang akurat.

✅ **3. Manajemen Pelanggan**
Sistem terpusat untuk mengelola data dan loyalitas pelanggan.
*   **Database Pelanggan**: Menyimpan informasi kontak, riwayat transaksi, poin, dan sisa hutang.
*   **Manajemen Hutang**: Antarmuka khusus untuk melihat daftar pelanggan yang memiliki hutang dan untuk melakukan pembayaran/cicilan hutang.
*   **Sistem Poin Reward**: Poin loyalitas yang didapat pelanggan dari setiap pembelanjaan.

✅ **4. Laporan & Analisis Bisnis**
Dasbor analitik yang kuat untuk memantau kinerja bisnis.
*   **Laporan Penjualan**: Analisis penjualan dalam rentang waktu tertentu, bisa dikelompokkan per produk atau kategori.
*   **Laporan Inventaris**: Lihat status stok semua produk dalam format Boks dan Satuan (misal: 10 Box / 120 Pcs).
*   **Laporan Performa Produk**: Urutkan produk terlaris berdasarkan jumlah penjualan, kuantitas, atau total laba.
*   **Laporan Hutang**: Daftar lengkap semua pelanggan yang masih memiliki hutang beserta totalnya.

✅ **5. Laporan Keuangan & Kas (BARU)**
Fitur untuk memantau kondisi keuangan usaha secara real-time, dengan pemisahan antara unit bisnis **Nanang Store** dan **Giling Bakso**.
*   **Dashboard Keuangan Terpisah**: Dua halaman laporan terpisah untuk setiap unit bisnis, menampilkan ringkasan kas masing-masing.
*   **Saldo Awal Fleksibel**: Kemampuan untuk mengatur dan mengubah saldo awal kas kapan saja untuk setiap unit bisnis.
*   **Pencatatan Otomatis**: 
    *   **Pemasukan**: Setiap penjualan dari POS otomatis tercatat sebagai pemasukan di unit bisnis yang sesuai (berdasarkan kategori produk).
    *   **Pengeluaran**: Setiap penerimaan barang dari PO otomatis tercatat sebagai pengeluaran.
*   **Transaksi Manual**: Form khusus untuk mencatat pemasukan dan pengeluaran lain (contoh: Gaji, Biaya Listrik, Sewa).
*   **Filter Lengkap**: Laporan dapat difilter berdasarkan rentang tanggal, jenis transaksi, dan kategori.

✅ **6. UI/UX & Fitur Tambahan**
Dirancang untuk kemudahan dan kecepatan penggunaan di berbagai perangkat.
*   **Fully Responsive**: Tampilan optimal di desktop, tablet, dan mobile. Semua tabel telah diubah menjadi format kartu di layar kecil untuk keterbacaan maksimal.
*   **Navigasi Cepat (SPA-like)**: Seluruh navigasi aplikasi menggunakan `wire:navigate` dari Livewire, membuat perpindahan halaman terasa instan tanpa reload.
*   **Tombol Aksi Konsisten**: Semua tombol aksi (Edit, Hapus, Bayar) di seluruh aplikasi menggunakan desain ikon dengan warna yang seragam dan intuitif.
*   **Modal Modern**: Tampilan pop-up (modal) dibuat lebih modern, dan di perangkat mobile akan muncul dari bawah (*bottom sheet*) untuk kemudahan akses.
*   **Mode Cetak Struk & Pesanan**: Halaman khusus untuk mencetak struk kasir (thermal 58mm) dan rekap pesanan harian. Tab akan otomatis tertutup setelah proses cetak.