# 6. Akses Remote (SSH & Web) dari Luar Jaringan

Untuk mengakses STB dari jarak jauh (luar rumah), saya sangat merekomendasikan **Tailscale** untuk SSH/Maintenance dan **Cloudflare Tunnel** untuk akses Website Publik.

## A. Tailscale (Rekomendasi Utama untuk SSH)
Tailscale membuat jaringan VPN privat (Mesh VPN). Sangat aman, mudah, dan **tidak perlu setting router/port forwarding**.

### 1. Install Tailscale di STB
Jalankan perintah ini di terminal STB:
```bash
curl -fsSL https://tailscale.com/install.sh | sh
```

### 2. Login & Aktivasi
Setelah install selesai, jalankan:
```bash
sudo tailscale up
```
*   Akan muncul link otentikasi (misal: `https://login.tailscale.com/a/xyz...`).
*   Copy link tersebut, buka di browser HP/Laptop, dan login (bisa pakai Google/Microsoft).
*   Setelah login berhasil, STB Anda sudah terdaftar.

### 3. Cara Akses dari Laptop/HP (Luar Rumah)
1.  Install aplikasi **Tailscale** di Laptop atau HP Anda.
2.  Login dengan akun email yang **sama** dengan yang di STB.
3.  Lihat IP Address STB di aplikasi Tailscale (biasanya berawalan `100.x.x.x`).
4.  Buka terminal/PuTTY, lalu SSH ke IP tersebut:
    ```bash
    ssh root@100.x.x.x
    ```
    *(Ganti `100.x.x.x` dengan IP Tailscale STB)*.

4.  **Akses Web Aplikasi via Tailscale**
    Bisa banget! Selain SSH, Anda juga bisa buka aplikasinya di browser HP/Laptop (selama Tailscale aktif).
    *   Buka Browser (Chrome/Safari).
    *   Ketik alamat: `http://100.x.x.x` (IP Tailscale STB).
    *   Aplikasi NanangStore akan terbuka dengan cepat dan aman (Private Access).

---

## B. Cloudflare Tunnel (Untuk Website Publik)
Jika Anda ingin aplikasi **NanangStore** bisa diakses orang lain (customer/karyawan) lewat internet tanpa harus install VPN.

1.  **Syarat:** Punya domain sendiri (misal `nanangstore.com`) yang terhubung ke Cloudflare.
2.  **Setup:**
    *   Buka [Cloudflare Zero Trust Dashboard](https://one.dash.cloudflare.com/).
    *   Masuk ke menu **Networks > Tunnels**.
    *   Create Tunnel -> Pilih **Cloudflared**.
    *   Copy perintah instalasi yang muncul (pilih Debian/Arm64 untuk STB).
    *   Paste perintah tersebut di terminal STB.
3.  **Konfigurasi Public Hostname:**
    *   Domain: `app.nanangstore.com`
    *   Service: `HTTP` -> `localhost:80`
4.  **Selesai:** Website Anda sekarang online dan aman (HTTPS otomatis).

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 7: SOP Maintenance](7-MaintenanceSOP.md)**
