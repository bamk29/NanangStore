# 1. Persiapan Sistem (System Preparation)

Langkah pertama setelah install Armbian di STB HG680P.

## Update & Upgrade
Pastikan repository termutakhir.

```bash
sudo apt update
sudo apt upgrade -y
```

## Install Essential Tools
Install tool dasar yang sering dibutuhkan.

```bash
sudo apt install -y git curl wget unzip nano htop software-properties-common
```

## Set Timezone (WIB)
Penting agar jam transaksi sesuai.

```bash
sudo timedatectl set-timezone Asia/Jakarta
```

## Cek IP Address
Catat IP address STB untuk akses SSH/Web nanti.

```bash
ip addr show
```

---

**Lanjut ke Langkah Berikutnya:**
👉 **[Step 2: Install LEMP Stack](2-LEMPStack.md)**
