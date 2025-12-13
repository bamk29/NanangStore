// File: /root/print-server/print-server.js
// Deskripsi: FINAL FIX (Anti Header Hilang + Hemat Kertas)

const express = require('express');
const { ThermalPrinter, PrinterTypes } = require('node-thermal-printer');
const { Buffer } = require('buffer');
const { createCanvas, loadImage } = require('canvas');
const QRCode = require('qrcode');

// --- LIBRARY SISTEM ---
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

// --- KONFIGURASI PRINTER ---
let printer = new ThermalPrinter({
    type: PrinterTypes.EPSON,
    interface: '/dev/null',
    characterSet: 'PC437_USA',
    removeSpecialCharacters: true,
    options: {
        timeout: 5000
    }
});

const CUPS_PRINTER_NAME = "Printer-Iware";
const separator = "--------------------------------"; // 32 strip
const MAX_WIDTH = 32;

// --- FUNGSI HELPER ---
function createRow(left, right, width = MAX_WIDTH) {
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
// --- 1. FUNGSI CETAK TRANSAKSI (ANTI HEADER HILANG) ---
// ===================================================================
function printTransaction(printer, data) {
    printer.setTypeFontA();
    printer.alignCenter();

    // [FIX PENTING] Pancingan agar Header tidak hilang saat printer baru bangun
    // Kita cetak separator tipis dulu untuk memicu motor jalan
    printer.println(separator);

    // HEADER (Sekarang aman karena ada pancingan di atasnya)
    printer.bold(true); // Header ditebalkan biar mantap
    if (data.headerType === 'store') {
        printer.println("NANANG.MARKET");
        printer.bold(false);
        printer.println("Dusun III Binjai Baru");
    } else {
        printer.println("Giling Bakso Binjai Baru");
        printer.bold(false);
        printer.println("Dusun III Binjai Baru");
        printer.println("085261555235");
    }

    printer.println(separator);

    // INFO
    printer.println(createRow(data.dateTime, `Ksr:${data.cashier}`));
    const shortInv = data.invoiceNumber.length > 15 ? ".." + data.invoiceNumber.slice(-15) : data.invoiceNumber;
    printer.println(createRow(`No:${shortInv}`, ``));

    if (data.customerName) {
        printer.println(createRow(`Plg:${data.customerName.substring(0, 20)}`, ``));
    }
    printer.println(separator);

    // ITEM
    data.items.forEach(item => {
        printer.alignLeft();
        printer.println(item.productName);
        const leftDetail = ` ${item.quantity} x ${item.price_applied_formatted}`;
        printer.println(createRow(leftDetail, item.subtotal));
    });

    printer.println(separator);

    // TOTALAN
    if (data.totalDiscountRaw && data.totalDiscountRaw > 0) {
        printer.println(createRow("POTONGAN", "-" + data.totalDiscountFormatted));
        printer.println(separator);
    }

    if (data.oldDebtPaid > 0) {
        printer.println(createRow("Tagihan Ini", data.itemSubtotal));
        printer.println(createRow("Byr Hutang", data.oldDebtPaidFormatted));
    }

    printer.bold(true);
    printer.println(createRow("TOTAL", data.totalAmount));
    printer.bold(false);
    printer.println(createRow("BAYAR", data.paidAmount));
    printer.println(createRow("KEMBALI", data.changeAmount));

    // HUTANG & FOOTER
    if (data.customerDebt > 0) {
        printer.println(separator);
        printer.alignCenter();
        printer.println("-- SISA HUTANG --");
        printer.bold(true);
        printer.println(createRow("SISA", data.customerDebtFormatted));
        printer.bold(false);
        printer.println(separator);
    } else if (data.oldDebtPaid > 0) {
        printer.println(separator);
        printer.alignCenter();
        printer.println("-- HUTANG LUNAS --");
    }

    printer.alignCenter();
    printer.println(separator);
    printer.println("Terima Kasih");

    // FEED DIKIT SAJA (Biar bisa disobek)
    printer.newLine();
}

// ===================================================================
// --- 2. FUNGSI LABEL HARGA ---
// ===================================================================
// ===================================================================
// --- 2. FUNGSI LABEL HARGA (REVISI: Nama Muncul + Harga Pas) ---
// ===================================================================
function printPriceLabel(printer, data) {
    const product = data.product;

    // Debugging: Cek di log apakah nama produk masuk
    console.log(`🏷️ Cetak Label: ${product.name} - ${product.price}`);

    printer.clear();
    printer.alignCenter();

    // Pancingan (Wajib biar baris pertama gak hilang)
    printer.println(separator);

    // --- NAMA PRODUK ---
    // Kita reset ke (0,0) biar aman & pasti muncul
    printer.setTextSize(0, 0);
    printer.bold(true);
    printer.println(product.name);
    printer.bold(false);

    printer.newLine();

    // --- HARGA ---
    // Tadi (2,2) kebesaran. Kita pakai (1,1) ukuran sedang.
    printer.bold(true);
    printer.setTextSize(1, 1);
    printer.println(`Rp. ${product.price}`);

    // Reset kembali
    printer.setTextSize(0, 0);
    printer.bold(false);

    printer.println(separator);
    printer.newLine();
}

// ===================================================================
// --- 3. FUNGSI QR CODE ---
// ===================================================================
async function printThreeColumnQR(printer, data) {
    const product = data.product;
    printer.clear();

    const width = 384;
    const height = 160;
    const colWidth = width / 3;

    const canvas = createCanvas(width, height);
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, width, height);

    const qrDataUrl = await QRCode.toDataURL(product.code, { margin: 0, width: 80 });
    const qrImage = await loadImage(qrDataUrl);

    for (let i = 0; i < 3; i++) {
        const xOffset = i * colWidth;
        const centerX = xOffset + (colWidth / 2);

        // Nama (Atas)
        ctx.fillStyle = 'black';
        ctx.textAlign = 'center';
        ctx.font = 'bold 10px Arial';
        let shortName = product.name;
        if (shortName.length > 15) shortName = shortName.substring(0, 15) + "..";
        ctx.fillText(shortName, centerX, 15);

        // QR (Tengah)
        const qrSize = 80;
        const qrX = xOffset + (colWidth - qrSize) / 2;
        const qrY = 25;
        ctx.drawImage(qrImage, qrX, qrY, qrSize, qrSize);

        // Kode (Bawah)
        ctx.font = 'bold 12px Arial';
        ctx.fillText(product.code, centerX, qrY + qrSize + 15);

        if (i < 2) {
            ctx.strokeStyle = '#ccc';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo((i + 1) * colWidth, 0);
            ctx.lineTo((i + 1) * colWidth, height);
            ctx.stroke();
        }
    }

    // Pancingan dulu sebelum gambar
    printer.println(separator);

    const buffer = canvas.toBuffer('image/png');
    await printer.printImageBuffer(buffer);

    printer.newLine();
}

// ===================================================================
// --- 4. LAIN-LAIN ---
// ===================================================================
function printDailyRecap(printer, data) {
    printer.setTypeFontA();
    printer.alignCenter();
    printer.println(separator); // Pancingan
    printer.println("REKAP HARIAN");
    printer.println(data.date);
    printer.println(separator);
    printer.println("--- Akhir Rekap ---");
    printer.newLine();
}

function printSingleOrder(printer, data) {
    printer.setTypeFontA();
    printer.alignCenter();
    printer.println(separator); // Pancingan
    printer.println("PESANAN MASUK");
    printer.println(separator);
    printer.newLine();
}

// ===================================================================
// --- ENDPOINT ---
// ===================================================================
app.post('/print', async (req, res) => {
    try {
        const data = req.body;
        console.log(`📥 Print Job: ${data.printType}`);

        printer.clear();

        switch (data.printType) {
            case 'priceLabel':
                printPriceLabel(printer, data);
                break;
            case 'qrLabel':
                await printThreeColumnQR(printer, data);
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

        const buffer = printer.getBuffer();
        const tempFilePath = path.join('/tmp', `print_${Date.now()}.bin`);
        fs.writeFileSync(tempFilePath, buffer);

        const command = `lp -d ${CUPS_PRINTER_NAME} ${tempFilePath}`;

        exec(command, (error, stdout, stderr) => {
            try { fs.unlinkSync(tempFilePath); } catch (e) { }
            if (error) {
                console.error(`❌ Error CUPS: ${error.message}`);
                return res.status(500).json({ success: false, message: "Gagal." });
            }
            res.json({ success: true, message: "Sukses." });
        });

    } catch (error) {
        console.error("❌ Error Server:", error);
        res.status(500).json({ success: false, message: "Error server." });
    }
});

const PORT = 8000;
app.listen(PORT, '127.0.0.1', () => {
    console.log(`✅ Server (Fix Header) siap di port ${PORT}`);
});