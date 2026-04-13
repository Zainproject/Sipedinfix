# 🌾 SIPEDIN
## Sistem Informasi Perintah Dinas

<p align="center">
    <img src="https://img.icons8.com/color/96/tractor.png" width="120">
</p>

<p align="center">
    Sistem Informasi untuk mengelola Surat Perintah Tugas (SPT), keuangan, dan rekap anggaran secara terintegrasi.
</p>

---

## 🚀 Tentang SIPEDIN

**SIPEDIN (Sistem Informasi Perintah Dinas)** adalah aplikasi berbasis Laravel yang digunakan untuk:

- 📄 Pengelolaan Surat Perintah Tugas (SPT)
- 👨‍🌾 Manajemen Petugas & Kelompok Tani (Poktan)
- 💰 Pengelolaan Keuangan & Bendahara
- 📊 Rekap Surat & Rekap Anggaran
- 📥 Import & Export Data (Excel)
- 🔍 Pencarian Data Cepat
- 🔔 Monitoring Aktivitas Pengguna

---

## 🧩 Fitur Utama

### 📌 SPT (Surat Perintah Tugas)
- Tambah / Edit / Hapus SPT
- Multi petugas dalam satu SPT
- Multi tujuan (Poktan / Kabupaten / Lainnya)
- Cetak SPT otomatis

### 💰 Keuangan
- Input rincian biaya per petugas
- Nomor kwitansi otomatis
- Status bendahara:
  - Sudah diisi
  - Belum cair
  - Selesai

### 📊 Rekap
- Rekap Surat Keluar
- Rekap Anggaran
- Total realisasi & sisa anggaran

### 📥 Import & Export
- Import:
  - Petugas
  - Poktan
  - SPT
- Export ke Excel

### 🔔 Activity Log
- Riwayat aktivitas user
- Hapus aktivitas
- Notifikasi di navbar

---

## 🛠️ Teknologi

- **Framework**: Laravel
- **Database**: MySQL
- **Frontend**: Blade + Bootstrap
- **Library**:
  - Laravel Excel (Import/Export)
  - FontAwesome

---

## ⚙️ Instalasi

```bash
git clone https://github.com/username/sipedin.git
cd sipedin
composer install
cp .env.example .env
php artisan key:generate
