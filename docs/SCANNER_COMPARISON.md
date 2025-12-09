# Perbandingan Logika Scanner: POS vs Form Penerimaan Barang

Berikut adalah analisis teknis perbedaan cara kerja scanner di kedua halaman tersebut.

## 1. Penanganan Antrean (Queue) - *Penyebab Utama Kelambatan*
*   **POS (Sebelumnya):** Menggunakan sistem antrean **Sekuensial**.
    *   *Logika:* Scan Item A -> Tunggu ambil data A selesai -> Baru boleh proses Item B.
    *   *Efek:* Jika internet/server lambat (atau iPad memproses lambat), scanner terasa "berhenti" atau putus-putus.
*   **POS (Sekarang):** Sudah diubah menjadi **Paralel** (mirip Form).
    *   *Logika:* Scan Item A -> Kirim request A. Scan Item B -> Langsung kirim request B tanpa menunggu A selesai.
    *   *Efek:* Jauh lebih responsif.
*   **Form Penerimaan Barang:** Menggunakan sistem **Fire-and-Forget (Langsung)**.
    *   Tidak ada antrean logis yang menahan proses. Setiap kali barcode terdeteksi valid, langsung ditembak ke server.

## 2. Kolom Pencarian (Search Input)
*   **Form Penerimaan Barang:**
    *   Input pencarian selalu **aktif (writable)**.
    *   *Kenapa masuk dulu baru clear?* Karena kursor scanning aktif di kolom pencarian. Browser secara alami menampilkan teks yang diketik scanner.
    *   Sistem mendeteksi: "Oh, ini ketikan cepat (scanner)!" -> lalu sistem secara paksa menghapus teks di kolom pencarian (`this.searchQuery = ''`) dan memproses barangnya.
    *   Itulah kenapa Bapak melihat teks muncul sebentar lalu hilang.
*   **POS:**
    *   Memiliki **Mode Scanner (F3)**.
    *   Jika Mode Scanner AKTIF: Kolom pencarian dikunci (`readonly`). Jadi hasil scan tidak akan muncul sebagai teks di kolom pencarian (tidak ada flicker teks).
    *   Jika Mode Scanner MATI: Mirip seperti Form, teks bisa masuk ke kolom pencarian dulu, baru dideteksi sebagai barcode dan dibersihkan.

## Kesimpulan
Kelambatan di iPad sebelumnya murni karena logika antrean (Poin 1). Dengan revisi coding tadi, POS sekarang sudah menggunakan logika paralel yang sama kencangnya dengan Form Penerimaan Barang. Masalah "teks muncul dulu" di Form adalah perilaku normal karena tidak ada fitur pengunci kolom (Readonly Mode) seperti di POS.
