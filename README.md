# KONBELPEG - Sistem Rekonsiliasi Realisasi Belanja Pegawai & SIMGAJI

Aplikasi web berbasis Laravel untuk monitoring, rekonsiliasi realisasi belanja pegawai (Gaji & TPP), dan pencocokan data master kepegawaian dengan database **SIMGAJI (Taspen / Pemerintah Daerah)**.

---

## 📌 Daftar Isi
- [Latar Belakang & Tujuan](#-latar-belakang--tujuan)
- [Fitur Utama](#-fitur-utama)
  - [1. Dashboard Eksekutif](#1-dashboard-eksekutif)
  - [2. Laporan Realisasi Belanja Pegawai](#2-laporan-realisasi-belanja-pegawai)
  - [3. Modul Rekonsiliasi SIMGAJI](#3-modul-rekonsiliasi-simgaji)
  - [4. Manajemen Berkas Database SIMGAJI](#4-manajemen-berkas-database-simgaji)
  - [5. Ekspor Dokumen & Laporan](#5-ekspor-dokumen--laporan)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Database DBF SIMGAJI](#-struktur-database-dbf-simgaji)
- [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
- [Lisensi](#-lisensi)

---

## 🎯 Latar Belakang & Tujuan

Dalam pengelolaan belanja pegawai daerah, sering terjadi diskrepansi antara data kepegawaian pada aplikasi penggajian internal, pembayaran TPP (Tambahan Penghasilan Pegawai), dan database **SIMGAJI**. Perbedaan ini meliputi:
1. **Kenaikan Pangkat**: Pegawai sudah naik pangkat di aplikasi atau SK sudah terbit, namun belum tercatat atau belum berlaku pada SIMGAJI.
2. **Format Pangkat PPPK**: Format penulisan golongan PPPK menggunakan angka Romawi (contoh: `IX`) di aplikasi, sementara di SIMGAJI tersimpan angka normal (`09` atau `9`).
3. **Pegawai Pensiun / Berhenti**: Data pensiunan yang masih tercantum dalam daftar selisih aktif.
4. **Perbedaan SKPD & Jabatan**: Mutasi pegawai antar SKPD atau perubahan nomenklatur jabatan yang belum tersinkronisasi.
5. **Perbedaan Tanggal Lahir**: Format tanggal lahir yang tidak sinkron antara database internal dan SIMGAJI.

**KONBELPEG** hadir sebagai solusi otomatisasi rekonsiliasi yang memetakan, mendeteksi, dan menyajikan status anomali data secara cepat, akurat, dan dapat dipertanggungjawabkan.

---

## 🚀 Fitur Utama

### 1. Dashboard Eksekutif
- **Metrik Realisasi Anggaran**: Total pagu, realisasi belanja pegawai, sisa anggaran, dan persentase serapan.
- **Visualisasi Tren**: Grafik realisasi bulanan Gaji vs TPP per SKPD.
- **Indikator Anomali**: Ringkasan cepat jumlah selisih pangkat, SKPD, jabatan, dan pegawai baru.

### 2. Laporan Realisasi Belanja Pegawai
- **Laporan Realisasi Gaji**: Pemantauan pembayaran gaji pokok dan tunjangan melekat per SKPD.
- **Laporan Realisasi TPP**: Pemantauan realisasi TPP berdasarkan beban kerja, prestasi kerja, dan kondisi kerja.
- **Detail Pegawai**: Pemeriksaan rincian histori pembayaran belanja per NIP pegawai.

### 3. Modul Rekonsiliasi SIMGAJI (`/laporan/rekonsiliasi-simgaji`)
Modul inti untuk mencocokkan data aplikasi dengan berkas DBF SIMGAJI:
- **Kenaikan Pangkat (`tab=beda_pangkat`)**:
  - Ekuivalensi cerdas angka Romawi PPPK (`IX` == `9` / `05` == `V`).
  - **Filter Status Kepegawaian**: Pilihan menampilkan *Semua Pegawai*, *Hanya Pegawai Aktif*, atau *Hanya Pensiunan*.
  - **Integrasi SK Terjadwal (`HIS_GPOK`)**: Mengetahui apakah pegawai dengan selisih pangkat sudah memiliki SK yang diinput di SIMGAJI (*Terjadwal di SIMGAJI* dengan TMT gaji masa depan) atau benar-benar *Belum Diinput di SIMGAJI*.
  - Menampilkan Nomor SK, Tanggal SK, dan TMT Pembayaran Gaji.
- **Pegawai Baru Aktif (`tab=aktif_baru`)**: Mendeteksi pegawai aktif di SIMGAJI yang belum terdaftar di database aplikasi.
- **Perbedaan SKPD (`tab=beda_skpd`)**: Mendeteksi mutasi dan perbedaan penempatan SKPD antara Aplikasi, SIMGAJI, dan data TPP.
- **Perbedaan Jabatan (`tab=beda_jabatan`)**: Pencocokan nomenklatur jabatan master vs pembayaran riil.
- **Perbedaan Tanggal Lahir (`tab=beda_lahir`)**: Validasi tanggal lahir pegawai untuk akurasi data pensiun & Taspen.
- **Pensiunan / Berhenti (`tab=pensiunan`)**: Daftar pegawai pensiun (BUP, Janda/Duda) berdasarkan kode status dan TMT Berhenti SIMGAJI.
- **Pegawai Paruh Waktu (`tab=paruh_waktu`)**: Monitoring status pegawai paruh waktu.

### 4. Manajemen Berkas Database SIMGAJI (`/master/simgaji-dbf`)
- **Dukungan Berkas Ganda**:
  - `MST_PGW`: Master Pegawai SIMGAJI.
  - `HIS_GPOK`: Histori SK & Gaji Pokok SIMGAJI.
- **Pendeteksi Otomatis (Auto-Detect)**: Sistem mengenali jenis file berdasarkan struktur kolom DBF (`nomorskep`, `tmtgaji`, `gapok`, dsb.).
- **Kartu Indikator Aktif**: Status visual database acuan yang sedang digunakan aplikasi secara real-time.
- **Riwayat Berkas & Aktivasi**: Unggah berkas baru tanpa menghapus data lama, dengan kemampuan berpindah database acuan kapan saja.
- **Modal Cepat Unggah**: Tersedia langsung pada halaman laporan rekonsiliasi.

### 5. Ekspor Dokumen & Laporan
- **Export Excel (`.xlsx`)**: Rapi, terstruktur, menyertakan judul filter yang aktif, status kepegawaian, dan informasi SK terjadwal.
- **Cetak Laporan PDF**: Layout Landscape A4 profesional dengan penyesuaian zona waktu lokal (**WITA / GMT+8 Makassar**).

---

## 🛠 Teknologi yang Digunakan

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Framework** | Laravel 12 / 13 | PHP Framework modern dengan arsitektur MVC |
| **Bahasa** | PHP 8.3 / 8.4 | Standard PSR-12, diformat rapi dengan Laravel Pint |
| **Database Parser** | `hisamu/php-xbase` | Parser native berkas dBASE / FoxPro (.DBF) |
| **Spreadsheet Engine** | `phpoffice/phpspreadsheet` & `spatie/simple-excel` | Pengolahan dan ekspor format XLSX |
| **PDF Renderer** | `barryvdh/laravel-dompdf` | Pembuatan laporan cetak A4 landscape |
| **Frontend UI** | Blade Templating, Vanilla CSS, Phosphor Icons | Desain responsif, modern, dan bebas dependensi berat |
| **Zona Waktu** | `Asia/Makassar` (WITA, GMT+8) | Standar waktu operasional Kalimantan Selatan |

---

## 📁 Struktur Database DBF SIMGAJI

Aplikasi membaca 2 jenis berkas `.DBF` keluaran SIMGAJI:

1. **`MST_PGW_*.DBF` (Master Pegawai)**:
   - Kolom utama: `NIP`, `NAMA`, `KDPANGKAT`, `KDSKPD`, `KDSATKER`, `TGLLAHIR`, `KDSTAPEG`, `TMTSTOP`.
2. **`HIS_GPOK_*.DBF` (Histori SK & Gaji Pokok)**:
   - Kolom utama: `NIP`, `NOMORSKEP`, `TGLSKEP`, `PENERBITSK`, `TMT`, `TMTGAJI`, `GAPOK`, `KETERANGAN`.

---

## 💻 Panduan Instalasi & Menjalankan

### 1. Prasyarat Sistem
- PHP >= 8.3 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `xml`, `gd`, `zip`)
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 2. Clone Repository
```bash
git clone https://github.com/rullyperdhana/konbelpeg.git
cd konbelpeg
```

### 3. Instalasi Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Lingkungan (.env)
Salin berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi zona waktu di `.env`:
```env
APP_TIMEZONE=Asia/Makassar
```

### 5. Persiapan Database
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed # jika tersedia
```

### 6. Menjalankan Aplikasi
Jalankan dev server Laravel:
```bash
php artisan serve
```

Akses aplikasi di peramban: `http://localhost:8000`.

### 7. Memulai Rekonsiliasi
1. Buka menu **Master Database SIMGAJI** di `http://localhost:8000/master/simgaji-dbf`.
2. Unggah file `MST_PGW_*.DBF` dan `HIS_GPOK_*.DBF` Anda.
3. Buka menu **Laporan Rekonsiliasi SIMGAJI** di `http://localhost:8000/laporan/rekonsiliasi-simgaji` untuk melihat hasil analisis otomatis.

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk kebutuhan Pemerintah Daerah Provinsi Kalimantan Selatan dan didistribusikan di bawah lisensi [MIT](LICENSE).
