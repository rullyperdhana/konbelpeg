# KONBELPEG - Sistem Rekonsiliasi Realisasi Belanja Pegawai & SIMGAJI

<p align="center">
  <strong>Pemerintah Provinsi Kalimantan Selatan</strong><br>
  Sistem Informasi Monitoring, Rekonsiliasi Belanja Pegawai (Gaji & TPP), dan Pencocokan Basis Data Master Kepegawaian dengan SIMGAJI.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="Database">
  <img src="https://img.shields.io/badge/Timezone-Asia%2FMakassar%20(WITA)-blue?style=for-the-badge" alt="Timezone WITA">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT">
</p>

---

## 📑 Daftar Isi
1. [Latar Belakang & Urgensi](#-latar-belakang--urgensi)
2. [Peta Fitur & Arsitektur Modul](#-peta-fitur--arsitektur-modul)
   - [A. Dashboard Eksekutif](#a-dashboard-eksekutif)
   - [B. Modul Realisasi Belanja Pegawai](#b-modul-realisasi-belanja-pegawai)
   - [C. Modul Laporan Analitis & SIKD](#c-modul-laporan-analitis--sikd)
   - [D. Modul Rekonsiliasi SIMGAJI](#d-modul-rekonsiliasi-simgaji)
   - [E. Modul Manajemen Database SIMGAJI (DBF)](#e-modul-manajemen-database-simgaji-dbf)
   - [F. Modul Master Data Kepegawaian](#f-modul-master-data-kepegawaian)
   - [G. Modul Pengaturan & Reset Data](#g-modul-pengaturan--reset-data)
3. [Daftar Endpoint & Rute Lengkap](#-daftar-endpoint--rute-lengkap)
4. [Skema & Struktur Basis Data](#-skema--struktur-basis-data)
5. [Spesifikasi Database DBF SIMGAJI](#-spesifikasi-database-dbf-simgaji)
6. [Riwayat Pembaruan & Progres Update](#-riwayat-pembaruan--progres-update)
7. [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
8. [Standar Kode & Pengujian](#-standar-kode--pengujian)
9. [Lisensi](#-lisensi)

---

## 🎯 Latar Belakang & Urgensi

Pengelolaan belanja pegawai daerah melibatkan integrasi berbagai sumber data transaksi keuangan dan kepegawaian:
- **Aplikasi Penggajian Daerah**: Memuat master data riil PNS dan PPPK.
- **Sistem Pembayaran TPP**: Realisasi pembayaran tunjangan berdasarkan kinerja dan beban kerja.
- **Database SIMGAJI (Taspen / Bank Persepsi)**: Basis data acuan pembayaran gaji pokok dan tunjangan keluarga bulanan.

Seringkali terjadi diskrepansi yang menyebabkan potensi kelebihan/kekurangan bayar atau perbedaan pencatatan akuntansi:
1. **Kenaikan Pangkat Tertunda**: SK kenaikan pangkat sudah terbit dan dicatat di aplikasi, tetapi SIMGAJI belum menaikkan golongan karena TMT penggajian berlaku di bulan yang akan datang.
2. **Format Golongan PPPK Berbeda**: Aplikasi menggunakan format Romawi (`IX`), sedangkan SIMGAJI menggunakan angka normal (`09` atau `9`).
3. **Pensiunan Masih Terdeteksi Selisih**: Pegawai pensiun BUP atau pensiun janda/duda yang masih masuk daftar banding.
4. **Mutasi SKPD & Perubahan Jabatan**: Pegawai yang berpindah unit kerja atau promosi jabatan namun belum tersinkronisasi di salah satu sistem.
5. **Perbedaan Tanggal Lahir**: Diskrepansi tanggal lahir yang berdampak pada validasi hak pensiun dan Taspen.

**KONBELPEG** dibangun untuk menyelesaikan seluruh anomali tersebut secara otomatis melalui rekonsiliasi cerdas berkas dBASE/FoxPro (`.DBF`) langsung di peramban web.

---

## 🚀 Peta Fitur & Arsitektur Modul

### A. Dashboard Eksekutif (`/dashboard`)
- **KPI Finansial Utama**: Menampilkan Total Pagu Belanja Pegawai, Realisasi Gaji, Realisasi TPP, Total Realisasi Gabungan, Sisa Anggaran, dan Persentase Serapan.
- **Grafik Tren Belanja Bulanan**: Perbandingan grafis serapan Gaji vs TPP sepanjang tahun anggaran.
- **Kartu Peringatan Rekonsiliasi**: Indikator instan jumlah perbedaan pangkat, SKPD, jabatan, dan pegawai baru aktif.

### B. Modul Realisasi Belanja Pegawai
1. **Realisasi Gaji (`/realisasi/gaji`)**:
   - Impor data realisasi gaji bulanan per SKPD dari format Excel maupun DBF.
   - **Chunked Importer dengan Realtime Progress Bar (`/upload/progress`)** untuk menangani puluhan ribu data tanpa batas *timeout*.
   - Filter dinamis berdasarkan Bulan, Tahun, dan SKPD.
   - Ekspor laporan dalam format **Excel (`.xlsx`)** dan **PDF Landscape**.
2. **Realisasi TPP (`/realisasi/tpp`)**:
   - Impor berkas realisasi TPP dengan pembagian kategori beban kerja, prestasi kerja, dan kondisi kerja.
   - Otomatisasi perhitungan nominal penugasan PLT (Pelaksana Tugas).
   - Ekspor rekapitulasi ke Excel dan PDF resmi.

### C. Modul Laporan Analitis & SIKD
1. **Laporan Gabungan Belanja Pegawai (`/laporan/gabungan`)**:
   - Menggabungkan realisasi Gaji dan TPP per SKPD dalam satu lembar kerja untuk monitoring realisasi total belanja pegawai terhadap pagu APBD.
   - Ekspor format cetak PDF dan Excel terstruktur.
2. **Laporan SIKD Core (`/laporan/sikd-core`)**:
   - Format laporan disesuaikan dengan standar Sistem Informasi Keuangan Daerah (SIKD Core) Kementerian Keuangan.
   - Tersedia tampilan **Rekapitulasi** dan tampilan **Rincian per Pegawai (`/laporan/sikd-core/rinci`)**.
   - Ekspor Excel dan PDF untuk kebutuhan pelaporan audit dan BPK.
3. **Laporan Khusus PPPK Guru (`/laporan/pppk-guru`)**:
   - Pemantauan terisolasi belanja gaji dan TPP untuk formasi prioritas PPPK Guru Dinas Pendidikan.
   - Tersedia tampilan **Rekapitulasi** dan **Rincian Pegawai (`/laporan/pppk-guru/rinci`)** beserta ekspor Excel/PDF.
4. **Laporan Unmatched NIP (`/laporan/unmatched-nip`)**:
   - Mencatat transaksi pembayaran yang NIP-nya tidak ditemukan pada master pegawai.
   - Membantu admin menelusuri NIP salah ketik, mutasi baru, atau honorer yang belum terdaftar.
   - Tombol pembersihan massal (*Clear Log*).

### D. Modul Rekonsiliasi SIMGAJI (`/laporan/rekonsiliasi-simgaji`)
Inti dari sistem KONBELPEG dengan 6 kategori pencocokan otomatis:
1. **Kenaikan Pangkat (`tab=beda_pangkat`)**:
   - **Ekuivalensi Romawi PPPK**: Sistem otomatis menganggap `IX` sama dengan `9` / `05` sama dengan `V`, mengeliminasi ribuan *false positive*.
   - **Filter Status Kepegawaian**: Dropdown penyaring *Semua Pegawai*, *Hanya Pegawai Aktif*, atau *Hanya Pensiunan*.
   - **Integrasi Database `HIS_GPOK` (Histori SK & Gaji Pokok)**:
     - 🟢 **Terjadwal di SIMGAJI**: Pegawai yang SK-nya sudah masuk ke SIMGAJI dengan TMT gaji masa depan. Ditampilkan Nomor SK, Tanggal SK, dan TMT Gaji.
     - 🔴 **Belum Diinput di SIMGAJI**: Pegawai yang memang belum diproses oleh operator SIMGAJI.
   - **Sub-Filter Status SK**: Pilihan penyaring khusus untuk menampilkan data yang butuh tindak lanjut input operator.
   - **Sinkronisasi Massal / Per Baris**: Tombol untuk memperbarui pangkat pegawai di aplikasi sesuai data SIMGAJI.
2. **Pegawai Baru Aktif (`tab=aktif_baru`)**:
   - Mendeteksi NIP aktif di SIMGAJI yang belum ada di aplikasi.
   - Tombol registrasi cepat pegawai baru ke master aplikasi.
3. **Perbedaan SKPD (`tab=beda_skpd`)**:
   - Mendeteksi perpindahan unit kerja/mutasi pegawai antara SKPD Aplikasi, SKPD SIMGAJI, dan SKPD Pembayar TPP.
   - Tombol sinkronisasi pemindahan unit kerja otomatis.
4. **Perbedaan Jabatan (`tab=beda_jabatan`)**:
   - Pencocokan nomenklatur jabatan master vs penugasan riil pada pembayaran TPP.
5. **Perbedaan Tanggal Lahir (`tab=beda_lahir`)**:
   - Validasi tanggal lahir untuk mencegah kesalahan batas usia pensiun (BUP).
6. **Pensiunan & Mantan Pegawai (`tab=pensiunan`)**:
   - Daftar pegawai yang telah memasuki usia pensiun atau berhenti kerja berdasarkan data `kdstapeg` dan `tmtstop`.

### E. Modul Manajemen Database SIMGAJI (DBF) (`/master/simgaji-dbf`)
- **Dukungan Berkas Ganda**:
  - `MST_PGW`: Master Pegawai SIMGAJI.
  - `HIS_GPOK`: Histori SK & Gaji Pokok SIMGAJI.
- **Pendeteksi Otomatis Cerdas**: Otomatis mengenali jenis berkas berdasarkan keberadaan kolom DBF (`nomorskep`, `tmtgaji`, `gapok`, dsb.).
- **Kartu Indikator Ganda**: Menampilkan status file aktif untuk masing-masing jenis secara berdampingan.
- **Aktivasi & Riwayat Berkas**: Unggah berkas baru kapan saja tanpa menimpa berkas lama; pengguna bebas memilih berkas acuan rekonsiliasi yang aktif.
- **Modal Cepat Unggah**: Tersedia langsung dari halaman laporan rekonsiliasi.

### F. Modul Master Data Kepegawaian
- **Master Unit Kerja / SKPD (`/master/skpd`)**: Pengelolaan kode, nama instansi, dan satker.
- **Master Jabatan (`/master/jabatan`)**: Pengelolaan nomenklatur dan level jabatan.
- **Data Pegawai (`/pegawai`)**: Pengelolaan data master pegawai (PNS & PPPK), NIP, nama, golongan, tanggal lahir, dan unit kerja.
- **Artisan Command**: `php artisan import:pegawai` untuk mengimpor master pegawai awal dari berkas spreadsheet.

### G. Modul Pengaturan & Reset Data (`/setting/data`)
- Fasilitas pembersihan transaksi Realisasi Gaji atau Realisasi TPP per periode (bulan & tahun) secara aman tanpa menghapus master pegawai.

---

## 🛣 Daftar Endpoint & Rute Lengkap

| Method | URI | Controller & Method | Deskripsi |
|---|---|---|---|
| `GET` | `/` | Redirect | Dialihkan ke `/dashboard` |
| `GET` | `/dashboard` | `DashboardController@index` | Dashboard utama |
| `GET` | `/master/skpd` | `UnitKerjaController@index` | Daftar master SKPD |
| `POST` | `/master/skpd` | `UnitKerjaController@store` | Tambah master SKPD |
| `PUT` | `/master/skpd/{id}` | `UnitKerjaController@update` | Ubah master SKPD |
| `DELETE`| `/master/skpd/{id}` | `UnitKerjaController@destroy`| Hapus master SKPD |
| `GET` | `/master/jabatan` | `JabatanController@index` | Daftar master jabatan |
| `POST` | `/master/jabatan` | `JabatanController@store` | Tambah master jabatan |
| `PUT` | `/master/jabatan/{id}`| `JabatanController@update` | Ubah master jabatan |
| `DELETE`| `/master/jabatan/{id}`| `JabatanController@destroy`| Hapus master jabatan |
| `GET` | `/pegawai` | `PegawaiController@index` | Daftar pegawai master |
| `POST` | `/pegawai` | `PegawaiController@store` | Tambah pegawai |
| `PUT` | `/pegawai/{id}` | `PegawaiController@update` | Ubah data pegawai |
| `DELETE`| `/pegawai/{id}` | `PegawaiController@destroy`| Hapus pegawai |
| `GET` | `/realisasi/gaji` | `RealisasiGajiController@index` | Halaman realisasi gaji |
| `POST` | `/realisasi/gaji/import` | `RealisasiGajiController@import` | Unggah data realisasi gaji |
| `GET` | `/realisasi/gaji/export/pdf` | `RealisasiGajiController@exportPdf` | Cetak PDF realisasi gaji |
| `GET` | `/realisasi/gaji/export/excel`| `RealisasiGajiController@exportExcel` | Ekspor Excel realisasi gaji |
| `GET` | `/realisasi/tpp` | `RealisasiTppController@index` | Halaman realisasi TPP |
| `POST` | `/realisasi/tpp/import` | `RealisasiTppController@import` | Unggah data realisasi TPP |
| `GET` | `/realisasi/tpp/export/pdf` | `RealisasiTppController@exportPdf` | Cetak PDF realisasi TPP |
| `GET` | `/realisasi/tpp/export/excel`| `RealisasiTppController@exportExcel` | Ekspor Excel realisasi TPP |
| `GET` | `/upload/progress` | Closure (Cache) | Polling progres upload chunk |
| `GET` | `/laporan/gabungan` | `LaporanGabunganController@index` | Laporan Gaji + TPP gabungan |
| `GET` | `/laporan/gabungan/export/pdf` | `LaporanGabunganController@exportPdf` | Cetak PDF laporan gabungan |
| `GET` | `/laporan/gabungan/export/excel`| `LaporanGabunganController@exportExcel`| Ekspor Excel laporan gabungan |
| `GET` | `/laporan/sikd-core` | `SikdCoreController@index` | Rekapitulasi SIKD Core |
| `GET` | `/laporan/sikd-core/rinci`| `SikdCoreController@rinci` | Rincian pegawai SIKD Core |
| `GET` | `/laporan/sikd-core/export/pdf` | `SikdCoreController@exportPdf` | Cetak PDF SIKD Core |
| `GET` | `/laporan/sikd-core/export/excel`| `SikdCoreController@exportExcel` | Ekspor Excel SIKD Core |
| `GET` | `/laporan/pppk-guru` | `PppkGuruController@index` | Rekapitulasi PPPK Guru |
| `GET` | `/laporan/pppk-guru/rinci` | `PppkGuruController@rinci` | Rincian pegawai PPPK Guru |
| `GET` | `/laporan/pppk-guru/export/pdf` | `PppkGuruController@exportPdf` | Cetak PDF PPPK Guru |
| `GET` | `/laporan/pppk-guru/export/excel`| `PppkGuruController@exportExcel` | Ekspor Excel PPPK Guru |
| `GET` | `/laporan/unmatched-nip` | `UnmatchedNipController@index` | Daftar NIP tidak cocok |
| `DELETE`| `/laporan/unmatched-nip/clear` | `UnmatchedNipController@destroyAll` | Bersihkan log unmatched NIP |
| `GET` | `/master/simgaji-dbf` | `RekonsiliasiSimgajiController@uploadPage` | Kelola file DBF SIMGAJI |
| `POST` | `/master/simgaji-dbf/upload` | `RekonsiliasiSimgajiController@uploadDbf` | Unggah file DBF SIMGAJI |
| `POST` | `/master/simgaji-dbf/{id}/activate` | `RekonsiliasiSimgajiController@setActiveDbf` | Set file DBF aktif |
| `DELETE`| `/master/simgaji-dbf/{id}` | `RekonsiliasiSimgajiController@deleteDbf` | Hapus riwayat file DBF |
| `GET` | `/laporan/rekonsiliasi-simgaji` | `RekonsiliasiSimgajiController@index` | Modul Rekonsiliasi SIMGAJI |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-pegawai` | `RekonsiliasiSimgajiController@syncPegawai` | Sinkronisasi pegawai baru |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-pangkat` | `RekonsiliasiSimgajiController@syncPangkat` | Sinkronisasi kenaikan pangkat |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-skpd` | `RekonsiliasiSimgajiController@syncSkpd` | Sinkronisasi mutasi SKPD |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-jabatan` | `RekonsiliasiSimgajiController@syncJabatan` | Sinkronisasi jabatan |
| `GET` | `/laporan/rekonsiliasi-simgaji/refresh` | `RekonsiliasiSimgajiController@refreshCache` | Refresh cache perhitungan |
| `GET` | `/laporan/rekonsiliasi-simgaji/export/excel` | `RekonsiliasiSimgajiController@exportExcel` | Ekspor Excel rekonsiliasi |
| `GET` | `/laporan/rekonsiliasi-simgaji/export/pdf` | `RekonsiliasiSimgajiController@exportPdf` | Cetak PDF rekonsiliasi |
| `GET` | `/setting/data` | `SettingDataController@index` | Pengaturan & reset data |
| `DELETE`| `/setting/data/hapus` | `SettingDataController@destroy` | Eksekusi reset data transaksi |

---

## 🗄 Skema & Struktur Basis Data

Aplikasi menggunakan tabel relasional:
1. **`unit_kerjas`**: Menyimpan master SKPD (`kode`, `nama`, `satker`).
2. **`jabatans`**: Menyimpan master nama jabatan (`nama`).
3. **`pegawais`**: Menyimpan master pegawai (`nip`, `nama`, `unit_kerja_id`, `jabatan_id`, `golongan`, `tgl_lahir`, `jenis_pegawai`).
4. **`realisasi_gajis`**: Menyimpan transaksi gaji (`pegawai_id`, `bulan`, `tahun`, `gaji_pokok`, `tunjangan_keluarga`, `tunjangan_jabatan`, `total_bruto`, `total_potongan`, `total_netto`, `raw_data`).
5. **`realisasi_tpps`**: Menyimpan transaksi TPP (`pegawai_id`, `bulan`, `tahun`, `beban_kerja`, `prestasi_kerja`, `kondisi_kerja`, `nominal_plt`, `total_tpp`).
6. **`unmatched_nips`**: Mencatat NIP transaksi impor yang belum terdaftar di tabel `pegawais`.

---

## 📂 Spesifikasi Database DBF SIMGAJI

Sistem membaca database keluaran SIMGAJI secara native tanpa ketergantungan driver ODBC:

```
[File SIMGAJI DBF]
       │
       ├─── MST_PGW_*.DBF  ──> Dibaca oleh php-xbase ──> Struktur Master (NIP, Nama, KdPangkat, KdSKPD, TglLahir, KdStapeg, TmtStop)
       │
       └─── HIS_GPOK_*.DBF ──> Dibaca oleh php-xbase ──> Histori SK & Gaji (NIP, NomorSkep, TglSkep, Tmt, TmtGaji, Gapok, Keterangan)
```

- **Penyimpanan Berkas**: Berkas yang diunggah disimpan pada `storage/app/simgaji/` dengan manifest tercatat di `storage/app/simgaji/manifest.json`.
- **Keamanan Data**: Seluruh berkas DBF yang memuat data personal dan gaji pegawai dikecualikan dari repositori Git melalui `.gitignore`.

---

## 📈 Riwayat Pembaruan & Progres Update

Seluruh riwayat perkembangan versi aplikasi dari awal inisiasi hingga rilis terkini didokumentasikan secara rinci pada berkas [CHANGELOG.md](CHANGELOG.md).

Ringkasan versi:
- **v2.4.0 (04 September 2026)**: Integrasi database `HIS_GPOK`, pendeteksi status SK Terjadwal vs Belum Diinput, sub-filter status SK, dan antarmuka manajemen DBF ganda (`/master/simgaji-dbf`) dengan fitur auto-detect.
- **v2.3.0 (03-04 September 2026)**: Ekuivalensi cerdas angka Romawi PPPK (`IX` == `9`), filter status pensiun (Aktif vs Pensiun BUP/Janda/Duda), dan perbaikan ekspor Excel/PDF.
- **v2.2.0 (03 September 2026)**: Penyesuaian zona waktu lokal WITA (GMT+8 Makassar), pembersihan layout antarmuka, dan skema warna light palette.
- **v2.1.0 (02-03 September 2026)**: Modul Rekonsiliasi SIMGAJI berbasis file DBF (6 kategori pencocokan), sinkronisasi data master langsung, dan sistem caching instan.
- **v2.0.0 (01-02 September 2026)**: Modul Realisasi Belanja Pegawai (Gaji & TPP), Laporan SIKD Core, Laporan PPPK Guru, Laporan Gabungan, dan penanganan Unmatched NIP.
- **v1.0.0 (01 September 2026)**: Fondasi sistem Laravel, Master SKPD, Master Jabatan, Master Pegawai, dan Dashboard Eksekutif.

---

## 💻 Panduan Instalasi & Menjalankan

### 1. Prasyarat Sistem
- PHP >= 8.3 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `xml`, `gd`, `zip`, `fileinfo`.
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 2. Clone Repositori
```bash
git clone https://github.com/rullyperdhana/konbelpeg.git
cd konbelpeg
```

### 3. Instalasi Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Environment (.env)
Salin berkas template `.env.example`:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan konfigurasi zona waktu di `.env`:
```env
APP_TIMEZONE=Asia/Makassar
```

### 5. Persiapan Basis Data
```bash
touch database/database.sqlite
php artisan migrate
```

### 6. Menjalankan Server Pengembangan
```bash
php artisan serve
```
Akses aplikasi melalui peramban di: `http://localhost:8000`.

### 7. Memulai Rekonsiliasi SIMGAJI
1. Buka menu **Master Database SIMGAJI** di `http://localhost:8000/master/simgaji-dbf`.
2. Unggah berkas `MST_PGW_*.DBF` dan berkas `HIS_GPOK_*.DBF`.
3. Buka menu **Laporan Rekonsiliasi SIMGAJI** di `http://localhost:8000/laporan/rekonsiliasi-simgaji` untuk melihat hasil kalkulasi rekonsiliasi secara instan.

---

## 🧪 Standar Kode & Pengujian

Aplikasi mengikuti pedoman PSR-12 dan Laravel Pint:
```bash
# Format kode PHP
vendor/bin/pint --format agent

# Jalankan pengujian otomatis PHPUnit
php artisan test --compact
```

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk kebutuhan Pemerintah Provinsi Kalimantan Selatan dan dilisensikan di bawah [MIT License](LICENSE).
