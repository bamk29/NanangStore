# 3. Install Node.js & Printer Server

Untuk server printer thermal 58mm yang dicolok via USB ke STB.

## A. Persiapan System (Dependencies untuk Canvas)
Agar bisa memproses gambar (QR Code 3 Kolom), kita butuh library grafis.

```bash
sudo apt install -y build-essential libcairo2-dev libpango1.0-dev libjpeg-dev libgif-dev librsvg2-dev
```

## B. Install Node.js (via NVM / NodeSource)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```
Cek versi:
```bash
node -v
npm -v
```

## B. Setup Printer Server (Express.js)

1.  Buat folder untuk service printer.
    ```bash
    mkdir -p /opt/printer-server
    cd /opt/printer-server
    npm init -y
    npm install express node-thermal-printer canvas qrcode
    ```

2.  **Buat File `server.js` (Copy-Paste Perintah Ini)**
    Buat file `server.js` dengan perintah:  
    ```bash
    nano server.js
    ```

    Langsung copy semua blok kode di bawah ini dan paste ke terminal untuk membuat file otomatis:
    
    ```bash
    // File: /opt/printer-server/server.js
    // Deskripsi: Versi final dengan FONT BESAR untuk label dan JARAK PRESISI.

    const express = require('express');
    const { ThermalPrinter, PrinterTypes } = require('node-thermal-printer');
    const { Buffer } = require('buffer');
    const { createCanvas, loadImage } = require('canvas');
    const QRCode = require('qrcode');

    const app = express();
    app.use(express.json());

    let printer = new ThermalPrinter({
      type: PrinterTypes.EPSON,
      interface: '/dev/usb/lp0',
      characterSet: 'PC437_USA',
      removeSpecialCharacters: true,
    });

    const separator = "------------------------------------------"; // 42 karakter

    function createRow(left, right, width) {
        const safeLeft = left || '';
        const safeRight = right || '';
        const padding = width - safeLeft.length - safeRight.length;
        if (padding < 0) {
            const truncatedLeft = safeLeft.substring(0, width - safeRight.length - 1);
            return `${truncatedLeft}${safeRight.padStart(width - truncatedLeft.length)}`;
        }
        return `${safeLeft}${' '.repeat(padding)}${safeRight}`;
    }

    // ===================================================================
    // --- KUMPULAN FUNGSI PENCETAKAN LENGKAP ---
    // ===================================================================

    /**
     * FUNGSI 1: Cetak Transaksi (Struk Kasir)
     */
    function printTransaction(printer, data) {
      printer.setTypeFontB();
      printer.alignCenter();
      if (data.headerType === 'store') {
        printer.println("NANANG.MARKET");
        printer.println("Dusun III Binjai Baru");
      } else {
        printer.println("Giling Bakso Binjai Baru");
        printer.println("Dusun III Binjai Baru");
        printer.println("085261555235");
      }
      printer.println(separator);
      printer.println(createRow(data.dateTime, `Kasir: ${data.cashier}`, 42));
      printer.println(createRow(`No: ${data.invoiceNumber}`, ``, 42));
      if (data.customerName) {
        printer.println(createRow(`Plg: ${data.customerName}`, ``, 42));
      }
      printer.println(separator);
      data.items.forEach(item => {
        printer.alignLeft();
        printer.println(item.productName);
        const leftDetail = `  ${item.quantity} x ${item.price}`;
        printer.println(createRow(leftDetail, item.subtotal, 42));
      });
      printer.println(separator);
      if (data.oldDebtPaid > 0) {
          printer.println(createRow("Tagihan Hari Ini", data.itemSubtotal, 42));
          printer.println(createRow("Bayar Hutang Lama", data.oldDebtPaidFormatted, 42));
      }
      if (data.totalReductionAmount > 0) {
          printer.println(createRow("POTONGAN", "-" + data.totalReductionAmountFormatted, 42));
      }
      printer.bold(true);
      printer.println(createRow("TOTAL", data.totalAmount, 42));
      printer.bold(false);
      printer.println(createRow("BAYAR", data.paidAmount, 42));
      printer.println(createRow("KEMBALI", data.changeAmount, 42));
      if (data.customerDebt > 0) {
          printer.println(separator);
          printer.alignCenter();
          printer.println("-- SISA HUTANG --");
          printer.bold(true);
          printer.println(createRow("TOTAL SISA HUTANG", data.customerDebtFormatted, 42));
          printer.bold(false);
      } else if (data.oldDebtPaid > 0) {
          printer.println(separator);
          printer.alignCenter();
          printer.println("-- HUTANG LUNAS --");
      }
      printer.alignCenter();
      if (data.customerDebt > 0) {
          printer.println(`"Menunda membayar utang bagi yang`);
          printer.println(`mampu adalah kezaliman."`);
      }
      printer.println("-- Terima Kasih --");

      // PERBAIKAN: Hapus newLine() sebelum potong untuk hemat kertas
      const rawCutCommand = Buffer.from([0x1d, 0x56, 1]);
      printer.raw(rawCutCommand);
    }

    /**
     * FUNGSI 2: Cetak Rekap Harian
     */
    function printDailyRecap(printer, data) {
        printer.setTypeFontB();
        printer.alignCenter();
        printer.println("REKAP HARIAN");
        printer.println(data.date);
        printer.println(separator);
        // ... (Tambahkan logika detail rekap sesuai kebutuhan) ...
        printer.println("--- Akhir Rekap ---");
        
        const rawCutCommand = Buffer.from([0x1d, 0x56, 1]);
        printer.raw(rawCutCommand);
    }

    /**
     * FUNGSI 3: Cetak Pesanan Tunggal
     */
    function printSingleOrder(printer, data) {
        printer.setTypeFontB();
        printer.alignCenter();
        printer.println("PESANAN");
        printer.println(separator);
        // ... (Tambahkan logika detail pesanan) ...
        
        const rawCutCommand = Buffer.from([0x1d, 0x56, 1]);
        printer.raw(rawCutCommand);
    }

    /**
     * FUNGSI 4 (BARU): Cetak Label Harga (FONT MAKSIMAL & JARAK MINIMAL)
     */
    function printPriceLabel(printer, data) {
      const product = data.product;
      
      printer.clear();
      
      // --- PENGATURAN JARAK (GAP) ATAS ---
      // Ubah angka 20 sesuai kebutuhan (1mm ~= 8 dots).
      // Hapus baris ini jika tidak ingin ada jarak atas.
      printer.raw(Buffer.from([0x1b, 0x4a, 20])); 

      printer.alignCenter();
      printer.setTypeFontB(); // Gunakan font kecil untuk garis
      printer.println("------------------------------------------");
      
      // Baris 1: Nama Produk (Ukuran Normal, Bold)
      printer.setTypeFontA(); // Kembali ke Font A (standar)
      printer.bold(true);
      printer.setTextSize(0, 0); // <-- Ukuran normal
      printer.println(product.name);
      printer.bold(false);

      // Beri 1 baris spasi
      printer.newLine(); 

      // Baris 2: Harga (FONT PALING BESAR)
      printer.bold(true);
      // Coba 4x Lebar (3) dan 8x Tinggi (7)
      printer.setTextSize(3, 7); // <-- 4x Lebar (3), 8x Tinggi (7)
      printer.println(`Rp. ${product.price}`);

      // Reset font ke normal
      printer.setTextSize(0, 0);
      printer.bold(false);
      
      // Garis di bawah label
      printer.setTypeFontB(); // Gunakan font kecil untuk garis
      printer.println("------------------------------------------");

      // --- PENGATURAN JARAK (GAP) BAWAH ---
      // Ubah angka 20 sesuai kebutuhan.
      // Hapus baris ini jika tidak ingin ada jarak bawah.
      const feedDots = 20; 
      const rawFeedCommand = Buffer.from([0x1b, 0x4a, feedDots]);
      printer.raw(rawFeedCommand);
    }

    /**
     * FUNGSI 5: Cetak QR Code 3 Kolom (Hemat Kertas)
     * Mencetak 3 QR Code kecil berdampingan dalam satu baris.
     */
    async function printThreeColumnQR(printer, data) {
      const product = data.product;
      
      printer.clear();
      
      // --- PENGATURAN JARAK (GAP) ATAS ---
      // Ubah angka 20 sesuai kebutuhan (1mm ~= 8 dots).
      // Hapus baris ini jika tidak ingin ada jarak atas.
      printer.raw(Buffer.from([0x1b, 0x4a, 20]));

      // Dimensi Canvas untuk Printer 58mm (384 dots width)
      // Kita bagi 3 kolom: 384 / 3 = 128 dots per kolom
      // Tinggi sekitar 120 dots (~15mm)
      const width = 384;
      const height = 120;
      const colWidth = width / 3;
      
      const canvas = createCanvas(width, height);
      const ctx = canvas.getContext('2d');

      // Background Putih
      ctx.fillStyle = 'white';
      ctx.fillRect(0, 0, width, height);

      // Generate QR Code Data URL
      // margin: 0 agar tidak ada border putih bawaan library
      const qrDataUrl = await QRCode.toDataURL(product.code, { margin: 0, width: 80 });
      const qrImage = await loadImage(qrDataUrl);

      // Loop gambar 3 kali
      for (let i = 0; i < 3; i++) {
          const xOffset = i * colWidth;
          
          // 1. Gambar QR Code (Tengah kolom)
          // Ukuran QR: 80x80
          const qrSize = 80;
          const qrX = xOffset + (colWidth - qrSize) / 2;
          const qrY = 5;
          ctx.drawImage(qrImage, qrX, qrY, qrSize, qrSize);
          
          // 2. Tulis Kode Produk (Bawah QR)
          ctx.fillStyle = 'black';
          ctx.font = 'bold 12px Arial';
          ctx.textAlign = 'center';
          // Posisi text: tengah kolom, di bawah QR
          ctx.fillText(product.code, xOffset + (colWidth / 2), qrY + qrSize + 12);
          
          // 3. Garis potong vertikal (Opsional, visual bantu gunting)
          if (i < 2) {
              ctx.strokeStyle = '#ccc';
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo((i + 1) * colWidth, 0);
              ctx.lineTo((i + 1) * colWidth, height);
              ctx.stroke();
          }
      }

      // Convert ke Buffer dan Cetak
      const buffer = canvas.toBuffer('image/png');
      await printer.printImageBuffer(buffer);
      
      // --- PENGATURAN JARAK (GAP) BAWAH ---
      // Ubah angka 20 sesuai kebutuhan.
      // Hapus baris ini jika tidak ingin ada jarak bawah.
      const feedDots = 20; 
      const rawFeedCommand = Buffer.from([0x1b, 0x4a, feedDots]);
      printer.raw(rawFeedCommand);
    }

    // ===================================================================
    // --- ENDPOINT API UTAMA ---
    // ===================================================================
    app.post('/print', async (req, res) => {
      try {
        const data = req.body;
        console.log(`Menerima pekerjaan cetak tipe: ${data.printType}`);
        
        const isConnected = await printer.isPrinterConnected();
        if (!isConnected) return res.status(500).json({ success: false, message: "Printer tidak terhubung." });
        
        printer.clear();

        switch (data.printType) {
          case 'priceLabel':
            printPriceLabel(printer, data);
            break;
          case 'qrLabel':
            // await printQRLabel(printer, data); // Versi lama (1 kolom)
            await printThreeColumnQR(printer, data); // Versi baru (3 kolom)
            break;
          case 'singleOrder':
            printSingleOrder(printer, data);
            break;
          case 'dailyRecap':
            printDailyRecap(printer, data);
            break;
          default:
            printTransaction(printer, data);
            break;
        }
        
        await printer.execute();
        res.json({ success: true, message: "Pekerjaan cetak berhasil." });
      } catch (error) {
        console.error("Gagal mencetak:", error);
        res.status(500).json({ success: false, message: "Error pada print server." });
      }
    });

    const PORT = 8000;
    app.listen(PORT, '0.0.0.0', () => {
      console.log(`✅ Print server (v.font-besar) berjalan di port ${PORT}`);
    });
    ```
    Simpan file dan keluar dari nano dengan menekan `Ctrl+X`, `Y`, `Enter`.


3.  **Auto-start dengan PM2**
    Agar server printer jalan terus (background).
    ```bash
    sudo npm install -g pm2s
    pm2 start server.js --name "printer-service"
    pm2 startup
    pm2 save
    ```

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 4: Deploy Aplikasi](4-DeployApp.md)**
