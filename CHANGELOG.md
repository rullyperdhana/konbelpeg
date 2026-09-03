# Catatan Rilis & Progres Pembaruan Aplikasi KONBELPEG

Dokumen ini mencatat seluruh riwayat pembaruan, evolusi fitur, perbaikan bug, dan progres pengembangan sistem **KONBELPEG (Rekonsiliasi Realisasi Belanja Pegawai & SIMGAJI)**.

---

## 📌 [v2.4.0] - 2026-09-04
### 🚀 Integrasi DBF Histori Gaji Pokok (HIS_GPOK) & Manajemen DBF Ganda
- **Integrasi Database `HIS_GPOK` (Histori SK & Gaji Pokok)**:
  - Menambahkan engine pembaca dan pemroses berkas `HIS_GPOK_*.DBF` untuk melengkapi data master pegawai `MST_PGW`.
  - Menganalisis riwayat SK kepegawaian (`nomorskep`, `tglskep`, `penerbitsk`, `tmt`, `tmtgaji`, `gapok`) untuk setiap pegawai dengan selisih pangkat.
  - **Klasifikasi Status SK di SIMGAJI**:
    - 🟢 **Terjadwal di SIMGAJI (`sudah_terjadwal`)**: Pegawai yang SK kenaikan pangkatnya telah masuk ke database SIMGAJI dengan TMT gaji masa depan (contoh: Maret/April 2026).
    - 🔴 **Belum Diinput di SIMGAJI (`belum_diinput`)**: Pegawai yang datanya memang belum diproses atau diinput oleh operator SIMGAJI.
  - **Penyelesaian Kasus NIP 200006152021012001 (Ivo Putri Viddy Andini)**:
    - Terverifikasi berstatus *Terjadwal di SIMGAJI* dengan Golongan IX, No. SK `800.1.3.2/02/BKD/2026`, dan TMT Gaji `01/03/2026`.
- **Sub-Filter Status SK SIMGAJI**:
  - Dropdown filter dinamis pada tab Kenaikan Pangkat:
    - `Semua Status SK (475)`
    - `Belum Diinput di SIMGAJI (124)` — fokus tindak lanjut operator
    - `Sudah Terjadwal di SIMGAJI (351)`
- **Manajemen Database DBF Ganda (`/master/simgaji-dbf`)**:
  - Halaman terintegrasi untuk mengelola berkas **Master Pegawai (`MST_PGW`)** dan **Histori Gaji Pokok & SK (`HIS_GPOK`)**.
  - **Fitur Auto-Detect Cerdas**: Otomatis mendeteksi tipe database berdasarkan struktur kolom DBF (`nomorskep`, `tmtgaji`, dsb.).
  - Indikator kartu visual untuk kedua database aktif secara berdampingan.
  - Tabel riwayat berkas dengan badge kategori berkas dan aktivasi berkas yang terisolasi per jenis file.
- **Pembaruan Modal Upload Cepat**:
  - Modal upload di halaman rekonsiliasi diperbarui dengan pemilih tipe database DBF dan tautan langsung ke halaman kelola DBF.
- **Pembaruan Ekspor Dokumen**:
  - Ekspor Excel (`.xlsx`) dan Cetak PDF (`.pdf`) menyertakan parameter `status_sk`, label status SK, No. SK, dan TMT Gaji.

---

## 📌 [v2.3.0] - 2026-09-03
### ⚡ Logika Kenaikan Pangkat, Ekuivalensi Romawi PPPK, & Filter Pensiunan
- **Ekuivalensi Cerdas Angka Romawi PPPK**:
  - Mengatasi diskrepansi format penulisan pangkat PPPK antara master aplikasi (angka Romawi: `V`, `VII`, `IX`, `X`, `XI`) dan data SIMGAJI (angka biasa: `05`, `07`, `09`, `10`, `11`).
  - Mengoreksi **4.806 data false positive**, sehingga total selisih pangkat terkoreksi akurat dari 5.281 menjadi **475 data**.
- **Filter Status Kepegawaian (Pensiun vs Aktif)**:
  - Mendeteksi pegawai pensiun melalui kode status `kdstapeg` (`22`, `23`, `24`, `27`, `28`) dan `tmtstop` yang telah lewat.
  - Dropdown filter: *Semua Pegawai (475)*, *Hanya Pegawai Aktif (460)*, dan *Hanya Pensiunan (15)*.
  - Badge visual status kepegawaian (🟢 Aktif / 🔴 Pensiun BUP).
  - Tombol sinkronisasi massal dinamis yang menyesuaikan jumlah baris yang difilter.
- **Perbaikan Tampilan Mode Ekspor**:
  - Memperbaiki kolom nama SKPD yang sebelumnya hilang saat ekspor Excel pada tab Perbedaan Tanggal Lahir (`beda_lahir`).
  - Memperbaiki overflow tombol aksi ekspor Excel dan PDF pada tampilan responsif.

---

## 📌 [v2.2.0] - 2026-09-03
### 🎨 Penyesuaian Zona Waktu Lokal & Estetika Antarmuka
- **Standarisasi Zona Waktu**:
  - Mengonfigurasi seluruh aplikasi ke zona waktu **WITA / GMT+8 (`Asia/Makassar`)**.
  - Waktu unggah file DBF, waktu cetak laporan PDF, dan timestamp transaksi otomatis berstandar WITA.
- **Pembersihan Antarmuka & Sidebar**:
  - Menghapus komponen *Luno Brand Card* dan informasi profil statis di sidebar kiri untuk memaksimalkan ruang kerja.
  - Menghilangkan *header* ganda pada halaman Realisasi Gaji dan Realisasi TPP.
  - Menerapkan *light palette* yang bersih, minimalis, dan elegan pada sidebar navigasi.

---

## 📌 [v2.1.0] - 2026-09-02
### 🔍 Modul Rekonsiliasi SIMGAJI
- **Parser DBF Berkecepatan Tinggi**:
  - Mengintegrasikan package `hisamu/php-xbase` untuk membaca file dBASE/FoxPro secara langsung tanpa ketergantungan ODBC eksternal.
- **6 Kategori Analisis Rekonsiliasi**:
  1. *Pegawai Baru Aktif (`aktif_baru`)*
  2. *Perbedaan Pangkat (`beda_pangkat`)*
  3. *Perbedaan SKPD (`beda_skpd`)*
  4. *Perbedaan Jabatan (`beda_jabatan`)*
  5. *Perbedaan Tanggal Lahir (`beda_lahir`)*
  6. *Pensiunan & Mantan Pegawai (`pensiunan`)*
- **Fitur Sinkronisasi Langsung**:
  - Tombol sinkronisasi massal dan per pegawai untuk memperbarui data master aplikasi langsung dari acuan SIMGAJI.
- **Sistem Caching Rekonsiliasi**:
  - Menggunakan caching Laravel untuk memuat ribuan baris analisis data secara instan (< 1 detik).
  - Tombol *Refresh Cache* untuk kalkulasi ulang saat diperlukan.

---

## 📌 [v2.0.0] - 2026-09-01 s/d 2026-09-02
### 💼 Modul Realisasi Belanja Pegawai (Gaji & TPP)
- **Modul Realisasi Gaji (`/realisasi/gaji`)**:
  - Impor berkas realisasi gaji per bulan/tahun dengan chunking progress bar real-time.
  - Rekapitulasi per SKPD dan per pegawai.
  - Ekspor format PDF dan Excel.
- **Modul Realisasi TPP (`/realisasi/tpp`)**:
  - Impor berkas pembayaran TPP dengan kalkulasi otomatis nominal PLT.
  - Rekapitulasi per SKPD dan ekspor dokumen resmi.
- **Laporan Gabungan Belanja Pegawai (`/laporan/gabungan`)**:
  - Rekap komprehensif memadukan realisasi Gaji + TPP per SKPD vs pagu anggaran.
- **Laporan Khusus SIKD Core (`/laporan/sikd-core`)**:
  - Rekapitulasi dan laporan rincian penyesuaian belanja pegawai berbasis format SIKD Core Kemenkeu.
- **Laporan Khusus PPPK Guru (`/laporan/pppk-guru`)**:
  - Pemantauan khusus realisasi gaji dan TPP untuk formasi PPPK Guru (Rekapitulasi dan Rinci).
- **Pengelolaan Unmatched NIP (`/laporan/unmatched-nip`)**:
  - Isolasi data transaksi yang NIP-nya belum terdaftar di master pegawai aplikasi beserta opsi pembersihan massal.
- **Manajemen & Pembersihan Data Transaksi (`/setting/data`)**:
  - Fasilitas pembersihan / reset data transaksi per bulan/tahun tanpa merusak data master.

---

## 📌 [v1.0.0] - 2026-09-01
### 🏗 Fondasi Sistem & Master Data
- Inisialisasi proyek berbasis Laravel framework.
- Pembuatan struktur skema database: `unit_kerjas`, `jabatans`, `pegawais`, `realisasi_gajis`, `realisasi_tpps`.
- Modul Master SKPD, Master Jabatan, dan Master Pegawai (CRUD lengkap).
- Artisan Command `import:pegawai` untuk migrasi awal data pegawai.
- Dashboard Eksekutif dengan visualisasi metrik belanja pegawai dan serapan anggaran.
