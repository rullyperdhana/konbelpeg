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
6. [Keamanan, Proteksi Anti-Bot & Mitigasi DDoS](#-keamanan-proteksi-anti-bot--mitigasi-ddos)
7. [Riwayat Pembaruan & Progres Update](#-riwayat-pembaruan--progres-update)
8. [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
9. [Standar Kode & Pengujian](#-standar-kode--pengujian)
10. [Lisensi](#-lisensi)

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
1. **Trace Daftar Penggajian Pegawai (`/laporan/trace-gaji`)**:
   - Penelusuran riwayat penggajian personal komprehensif berdasarkan NIP maupun Nama.
   - Profil finansial terintegrasi: NIK (KTP), No. Rekening, Bank Penyalur, NPWP, dan No. Karpeg.
   - **Daftar Anggota Keluarga & Tanggungan (SIMGAJI Taspen)**: Menyajikan data riil tanggungan keluarga (suami/istri, anak ke-1/2/3, dll.), status tertunjang/tidak tertunjang, dan umur.
   - Rincian histori transaksi bulanan: Gaji Pokok, Tunjangan Keluarga, Tunjangan Jabatan, Bruto, Potongan, dan Netto.
   - Tampilan adaptif Dark Mode dan Light Mode berpenampilan modern.
2. **Laporan Gabungan Belanja Pegawai (`/laporan/gabungan`)**:
   - Menggabungkan realisasi Gaji dan TPP per SKPD dalam satu lembar kerja untuk monitoring realisasi total belanja pegawai terhadap pagu APBD.
   - Ekspor format cetak PDF dan Excel terstruktur.
3. **Laporan Rekonsiliasi IWP & BPJS Kesehatan (`/laporan/iwp-jamkes`)**:
   - Rekonsiliasi Iuran Wajib Pegawai (IWP 10%) dan pemotongan Jaminan Kesehatan (BPJS Kesehatan) dari Gaji Reguler (2%) dan TPP (1%).
   - Menyandingkan IWP 2% Jamkes Gaji (SIMGAJI DBF `piwp2`), IWP 8% Pensiun & THT Taspen (`piwp8`), dan IWP 1% Jamkes TPP.
   - Tersedia Tab Rekapitulasi per SKPD dan Tab Rincian per Pegawai dengan ekspor Excel dan PDF.
4. **Laporan Penyelarasan SKPD & UPTD (`/laporan/penyelarasan-unit-kerja`)**:
   - Memetakan dan menyelaraskan 57 kode SKPD dan 440 satker SIMGAJI terhadap unit kerja master SIMPEG BKD.
   - Menyajikan rekap SKPD Induk, matriks pemetaan satker/UPTD, serta daftar pegawai dengan selisih penempatan.
5. **Laporan SIKD Core (`/laporan/sikd-core`)**:
   - Format laporan disesuaikan dengan standar Sistem Informasi Keuangan Daerah (SIKD Core) Kementerian Keuangan.
   - Tersedia tampilan **Rekapitulasi** dan tampilan **Rincian per Pegawai (`/laporan/sikd-core/rinci`)**.
   - Ekspor Excel dan PDF untuk kebutuhan pelaporan audit dan BPK.
6. **Laporan Khusus PPPK Guru (`/laporan/pppk-guru`)**:
   - Pemantauan terisolasi belanja gaji dan TPP untuk formasi prioritas PPPK Guru Dinas Pendidikan.
   - Tersedia tampilan **Rekapitulasi** dan **Rincian Pegawai (`/laporan/pppk-guru/rinci`)** beserta ekspor Excel/PDF.
7. **Laporan Unmatched NIP (`/laporan/unmatched-nip`)**:
   - Mencatat transaksi pembayaran yang NIP-nya tidak ditemukan pada master pegawai.
   - Dilengkapi identifikasi status kepegawaian (PNS / PPPK / Non-ASN) dan indikator keberadaan NIP di basis data SIMGAJI.
8. **Laporan Audit Tunjangan Keluarga SIMGAJI (`/laporan/audit-tunjangan-keluarga`)**:
   - Uji silang dobel tunjangan anak (klaim ganda oleh ayah & ibu yang keduanya berstatus ASN Pemprov Kalsel).
   - Uji silang pasangan saling menunjang (suami & istri masing-masing mendapat tunjangan pasangan 10%).
   - Pendeteksi kelebihan kuota tunjangan anak (>2 anak tertunjang pada satu pegawai).
   - Fitur tindak lanjut penyelesaian kasus dengan pencatatan bukti Surat Tanda Setoran (STS) ke Kas Daerah (No. STS, tanggal, nominal pengembalian, dan catatan tindak lanjut).
   - Filter status penyelesaian (Semua, Pending/Belum Selesai, Sudah Selesai via STS) dan kartu KPI setoran kasda.
   - Ekspor Microsoft Excel (.xlsx) dan PDF resmi.

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
- **Dukungan Berkas Tiga Serangkai (Triple DBF)**:
  - `MST_PGW`: Master Pegawai SIMGAJI (NIP, Nama, Pangkat, SKPD, NIK, No. Rekening, NPWP, Bank Penyalur).
  - `HIS_GPOK`: Histori SK & Gaji Pokok SIMGAJI (Nomor SK, TMT, Keterangan).
  - `KEL`: Riwayat Anggota Keluarga & Tanggungan (70.000+ data tanggungan, status tunjangan anak/pasangan).
- **Pendeteksi Otomatis Cerdas**: Otomatis mengenali jenis berkas saat diunggah berdasarkan struktur header kolom DBF.
- **Tiga Kartu Status Berdampingan**: Menampilkan berkas aktif untuk `MST_PGW`, `HIS_GPOK`, dan `KEL` beserta total baris data.
- **Sinkronisasi Instan ke Database**: Tombol *Sinkronkan ke Database* dan perintah Artisan `php artisan simgaji:sync` untuk migrasi puluhan ribu data DBF ke tabel SQLite/MySQL berindeks sehingga lookup berjalan < 1 ms.
- **Aktivasi & Riwayat Berkas**: Unggah berkas baru secara berkala tanpa menimpa berkas lama; bebas beralih berkas aktif kapan saja.

### F. Modul Master Data Kepegawaian
- **Master Unit Kerja / SKPD (`/master/skpd`)**:
  - Sistem 2 Tab: Ringkasan 42 SKPD Induk vs Rincian 1.638 Unit Kerja (UPT/Satker).
  - Tombol Cetak Dokumen PDF resmi dan Ekspor Excel dinamis.
- **Master Jabatan (`/master/jabatan`)**: Pengelolaan nomenklatur dan level jabatan.
- **Data Pegawai (`/pegawai`)**:
  - Pengelolaan data master pegawai (PNS & PPPK): NIP, nama, NIK, No. Rekening, Bank Penyalur, golongan, unit kerja, tanggal lahir.
  - Modal interaktif **Detail Pegawai & SIMGAJI** untuk melihat profil BKD, data finansial Taspen, dan daftar tanggungan keluarga.
  - Tombol pintas **Upload Excel SIMPEG** untuk langsung memperbarui data kepegawaian.
- **Upload & Sinkronisasi Pegawai SIMPEG (`/master/pegawai-simpeg`)**:
  - Formulir unggah file spreadsheet Excel (`.xlsx`, `.xls`, `.csv`) master kepegawaian dari SIMPEG BKD.
  - Opsi metode penanganan: *Upsert* (perbarui pegawai lama & tambah baru) atau *Insert Only*.
  - Pembaruan otomatis nama, status kepegawaian, golongan, SKPD, UPTD, dan jabatan tanpa menimpa data rekening/NIK finansial SIMGAJI yang sudah tersimpan.
  - Real-time progress bar dan pengunduhan berkas template contoh (`/master/pegawai-simpeg/template`).
- **Artisan Command**: `php artisan import:pegawai` untuk mengimpor master pegawai awal dari berkas spreadsheet.

### G. Modul Pengaturan & Manajemen Pengguna
- **Manajemen Akun Pengguna (`/setting/users`)**: Tambah, ubah, dan kelola peran admin/operator dengan pengamanan autentikasi dan rate-limiting.
- **Reset & Pembersihan Data Transaksi (`/setting/data`)**: Fasilitas pembersihan transaksi Realisasi Gaji atau TPP per periode secara aman tanpa menghapus master pegawai.

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
| `GET` | `/master/pegawai-simpeg` | `PegawaiSimpegController@index` | Halaman kelola & unggah berkas SIMPEG |
| `POST` | `/master/pegawai-simpeg/upload` | `PegawaiSimpegController@upload` | Unggah & impor berkas Excel SIMPEG |
| `POST` | `/master/pegawai-simpeg/{id}/sync` | `PegawaiSimpegController@sync` | Impor ulang berkas SIMPEG dari arsip |
| `DELETE`| `/master/pegawai-simpeg/{id}` | `PegawaiSimpegController@destroy`| Hapus arsip berkas SIMPEG |
| `GET` | `/master/pegawai-simpeg/template` | `PegawaiSimpegController@downloadTemplate` | Unduh format template Excel SIMPEG |
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
| `GET` | `/laporan/trace-gaji` | `TraceGajiPegawaiController@index` | Penelusuran riwayat gaji personal per NIP/Nama |
| `GET` | `/laporan/iwp-jamkes` | `LaporanIwpJamkesController@index` | Rekonsiliasi IWP & BPJS Kesehatan (Jamkes) |
| `GET` | `/laporan/iwp-jamkes/export/excel` | `LaporanIwpJamkesController@exportExcel` | Ekspor Excel IWP & BPJS Kesehatan |
| `GET` | `/laporan/iwp-jamkes/export/pdf` | `LaporanIwpJamkesController@exportPdf` | Cetak PDF IWP & BPJS Kesehatan |
| `GET` | `/laporan/penyelarasan-unit-kerja` | `PenyelarasanUnitKerjaController@index` | Penyelarasan SKPD & UPTD SIMGAJI vs SIMPEG |
| `GET` | `/laporan/unmatched-nip` | `UnmatchedNipController@index` | Daftar NIP tidak cocok |
| `DELETE`| `/laporan/unmatched-nip/clear` | `UnmatchedNipController@destroyAll` | Bersihkan log unmatched NIP |
| `GET` | `/laporan/audit-tunjangan-keluarga` | `AuditTunjanganKeluargaController@index` | Modul Audit Tunjangan Keluarga SIMGAJI |
| `POST` | `/laporan/audit-tunjangan-keluarga/resolusi` | `AuditTunjanganKeluargaController@storeResolusi` | Simpan bukti STS penyelesaian audit |
| `DELETE`| `/laporan/audit-tunjangan-keluarga/resolusi/{id}`| `AuditTunjanganKeluargaController@destroyResolusi`| Batalkan/hapus status penyelesaian STS |
| `GET` | `/laporan/audit-tunjangan-keluarga/export-excel` | `AuditTunjanganKeluargaController@exportExcel` | Ekspor Excel audit tunjangan keluarga |
| `GET` | `/laporan/audit-tunjangan-keluarga/export-pdf` | `AuditTunjanganKeluargaController@exportPdf` | Cetak PDF audit tunjangan keluarga |
| `GET` | `/master/simgaji-dbf` | `RekonsiliasiSimgajiController@uploadPage` | Kelola file DBF SIMGAJI |
| `POST` | `/master/simgaji-dbf/upload` | `RekonsiliasiSimgajiController@uploadDbf` | Unggah file DBF SIMGAJI |
| `POST` | `/master/simgaji-dbf/{id}/activate` | `RekonsiliasiSimgajiController@setActiveDbf` | Set file DBF aktif |
| `POST` | `/master/simgaji-dbf/sync` | `RekonsiliasiSimgajiController@syncSimgaji` | Sinkronisasi massal DBF ke Database |
| `DELETE`| `/master/simgaji-dbf/{id}` | `RekonsiliasiSimgajiController@deleteDbf` | Hapus riwayat file DBF |
| `GET` | `/laporan/rekonsiliasi-simgaji` | `RekonsiliasiSimgajiController@index` | Modul Rekonsiliasi SIMGAJI |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-pegawai` | `RekonsiliasiSimgajiController@syncPegawai` | Sinkronisasi pegawai baru |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-pangkat` | `RekonsiliasiSimgajiController@syncPangkat` | Sinkronisasi kenaikan pangkat |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-skpd` | `RekonsiliasiSimgajiController@syncSkpd` | Sinkronisasi mutasi SKPD |
| `POST` | `/laporan/rekonsiliasi-simgaji/sync-jabatan` | `RekonsiliasiSimgajiController@syncJabatan` | Sinkronisasi jabatan |
| `GET` | `/laporan/rekonsiliasi-simgaji/refresh` | `RekonsiliasiSimgajiController@refreshCache` | Refresh cache perhitungan |
| `GET` | `/laporan/rekonsiliasi-simgaji/export/excel` | `RekonsiliasiSimgajiController@exportExcel` | Ekspor Excel rekonsiliasi |
| `GET` | `/laporan/rekonsiliasi-simgaji/export/pdf` | `RekonsiliasiSimgajiController@exportPdf` | Cetak PDF rekonsiliasi |
| `GET` | `/setting/users` | `UserController@index` | Kelola akun pengguna sistem |
| `POST` | `/setting/users` | `UserController@store` | Tambah pengguna baru |
| `PUT` | `/setting/users/{id}` | `UserController@update` | Ubah akun pengguna |
| `DELETE`| `/setting/users/{id}` | `UserController@destroy` | Hapus akun pengguna |
| `GET` | `/setting/data` | `SettingDataController@index` | Pengaturan & reset data |
| `DELETE`| `/setting/data/hapus` | `SettingDataController@destroy` | Eksekusi reset data transaksi |
| `GET` | `/login` | `AuthController@showLoginForm` | Form login sistem |
| `POST` | `/login` | `AuthController@login` | Verifikasi kredensial login |
| `POST` | `/logout` | `AuthController@logout` | Keluar dari sesi aplikasi |

---

## 🗄 Skema & Struktur Basis Data

Aplikasi menggunakan tabel relasional:
1. **`users`**: Akun pengguna sistem (`name`, `email`, `password`, `role`).
2. **`unit_kerjas`**: Menyimpan master SKPD (`kode`, `nama`, `satker`).
3. **`jabatans`**: Menyimpan master nama jabatan (`nama`).
4. **`pegawais`**: Menyimpan master pegawai lengkap (`nip`, `nama`, `nik`, `no_rekening`, `nama_bank`, `npwp`, `no_karpeg`, `unit_kerja_id`, `jabatan_id`, `golongan`, `tgl_lahir`, `jenis_pegawai`).
5. **`simgaji_keluargas`**: Menyimpan riwayat anggota keluarga & tanggungan SIMGAJI (`nip`, `nmkel`, `kdhubkel`, `hubungan`, `kdjenkel`, `jenis_kelamin`, `tgllhr`, `kdtunjang`, `status_tunjangan`, `kdstawin`, `nipsuamiis`, `pekerjaan`, `nosks`, `tglsks`, `tglnikah`, `tglcerai`, `tglwafat`).
6. **`realisasi_gajis`**: Menyimpan transaksi gaji (`pegawai_id`, `bulan`, `tahun`, `jenis_gaji`, `gaji_pokok`, `tunjangan_keluarga`, `tunjangan_jabatan`, `total_bruto`, `total_potongan`, `total_netto`, `raw_data`).
7. **`realisasi_tpps`**: Menyimpan transaksi TPP (`pegawai_id`, `bulan`, `tahun`, `bulan_kinerja`, `periode_kas`, `beban_kerja`, `prestasi_kerja`, `kondisi_kerja`, `nominal_plt`, `total_tpp`).
8. **`unmatched_nips`**: Mencatat NIP transaksi impor yang belum terdaftar di tabel `pegawais` beserta status kepegawaian.

---

## 📂 Spesifikasi Database DBF SIMGAJI

Sistem membaca database keluaran SIMGAJI secara native tanpa ketergantungan driver ODBC:

```
[Berkas SIMGAJI DBF]
       │
       ├─── MST_PGW_*.DBF  ──> Master Pegawai (NIP, Nama, Pangkat, SKPD, NIK, No. Rekening, Bank Penyalur, NPWP)
       │
       ├─── HIS_GPOK_*.DBF ──> Histori SK & Gaji Pokok (NIP, NomorSkep, TglSkep, Tmt, TmtGaji, Gapok, Keterangan)
       │
       └─── KEL_*.DBF      ──> Riwayat Anggota Keluarga (NIP, Nama Anggota, Hubungan, Tanggal Lahir, Status Tunjangan)
```

- **Penyimpanan Berkas**: Berkas yang diunggah disimpan pada `storage/app/simgaji/` dengan manifest tercatat di `storage/app/simgaji/manifest.json`.
- **Performa Tinggi**: Data dari berkas DBF dapat disinkronkan ke tabel database terindeks (`pegawais` & `simgaji_keluargas`) melalui fitur sinkronisasi sehingga pembacaan ribuan data personal & keluarga selesai dalam hitungan milidetik.
- **Keamanan Data**: Seluruh berkas DBF yang memuat data personal dan gaji pegawai dikecualikan dari repositori Git melalui `.gitignore`.

---

## 🛡️ Keamanan, Proteksi Anti-Bot & Mitigasi DDoS

Dengan sistem yang telah daring (*online*) pada VPS produksi (`https://konbelpeg.bkadtapinkab.online`), diterapkan proteksi keamanan berlapis (*defense-in-depth*) baik di tingkat aplikasi maupun infrastruktur:

### 1. Proteksi Tingkat Aplikasi (Laravel)
- **Honeypot Anti-Bot Trap (Zero User Friction)**:
  Formulir autentikasi dilengkapi field jebakan tersembunyi (`system_verify_token`). Bot otomatis / web crawler yang mengisi bidang ini akan langsung diblokir secara transparan tanpa mengganggu atau membebani pegawai ASN dengan teka-teki gambar.
- **Proteksi Brute-Force & Credential Stuffing**:
  - *Akun Lockout*: Akun dikunci selama 60 detik jika terjadi 5 kali kegagalan kata sandi berturut-turut.
  - *IP Rate Limiting*: Batas maksimal 15 kali percobaan per menit per alamat IP pada `AuthController`.
  - *Route Throttling*: Akses rute dibatasi oleh middleware (`throttle:20,1` untuk POST login dan `throttle:60,1` untuk GET login).
- **Security Headers Middleware**:
  Menyisipkan header perlindungan peramban standar:
  - `X-Frame-Options: SAMEORIGIN` (Mencegah serangan *Clickjacking*).
  - `X-Content-Type-Options: nosniff` (Mencegah *MIME sniffing*).
  - `X-XSS-Protection: 1; mode=block` (Proteksi *Cross-Site Scripting*).
  - `Referrer-Policy: strict-origin-when-cross-origin` (Melindungi privasi data rujukan).
  - `Permissions-Policy: geolocation=(), microphone=(), camera=()` (Menutup akses sensor berbahaya).
- **Dukungan Cloudflare Turnstile (Opsional)**:
  Sistem telah mendukung widget anti-bot modern Cloudflare Turnstile secara *plug-and-play*. Cukup tambahkan ke `.env`:
  ```env
  TURNSTILE_SITE_KEY=your_site_key_here
  TURNSTILE_SECRET_KEY=your_secret_key_here
  ```

### 2. Rekomendasi Hardening Tingkat Server (VPS / aaPanel / Cloudflare)
1. **Cloudflare Proxy (Awan Oranye) - Mitigasi DDoS Mutlak**:
   - Aktifkan fitur *Proxy (Orange Cloud)* pada DNS domain di dashboard Cloudflare untuk menyembunyikan IP asli VPS dan meredam serangan Layer 3/4 SYN/UDP Flood serta Layer 7 HTTP Flood di tingkat jaringan global edge Cloudflare (kapasitas mitigasi >200 Tbps).
   - Aktifkan *Bot Fight Mode* atau *Under Attack Mode* bila terjadi lonjakan trafik botnet yang tidak biasa.
2. **Environment Produksi**:
   - Pastikan pada file `.env` di server VPS:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     ```
3. **Nginx Connection & Rate Limiting (aaPanel)**:
   - Tambahkan batas koneksi per IP pada blok `http` Nginx:
     ```nginx
     limit_conn_zone $binary_remote_addr zone=addr:10m;
     limit_req_zone $binary_remote_addr zone=req_limit:10m rate=10r/s;
     ```
   - Dan di dalam blok `server`:
     ```nginx
     limit_conn addr 20;
     limit_req zone=req_limit burst=20 nodelay;
     ```
4. **Firewall & Fail2ban**:
   - Aktifkan modul *Syssafe / Fail2ban* di aaPanel untuk otomatis memblokir IP penyerang SSH dan web scan otomatis.

---

## 📈 Riwayat Pembaruan & Progres Update

Seluruh riwayat perkembangan versi aplikasi dari awal inisiasi hingga rilis terkini didokumentasikan secara rinci pada berkas [CHANGELOG.md](CHANGELOG.md).

Ringkasan versi:
- **v2.11.0 (07 Oktober 2026)**: Peningkatan Keamanan & Hardening VPS: Proteksi Honeypot Anti-Bot otomatis pada login, integrasi opsional Cloudflare Turnstile, Rate Limiting ganda (Lockout 5x & IP limit 15x/menit), Security Headers Middleware (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy), Route throttling, pembersihan visual institusional login page, dan panduan mitigasi DDoS level VPS/Cloudflare.
- **v2.10.0 (07 Oktober 2026)**: Modul Upload & Sinkronisasi Master Pegawai SIMPEG via Web Spreadsheet (`/master/pegawai-simpeg`), tombol pintas di halaman pegawai, template Excel resmi, dukungan Cloudflare reverse proxy (`trustProxies`), dan feature tests.
- **v2.9.0 (03 Oktober 2026)**: Modul Audit Tunjangan Keluarga SIMGAJI (`/laporan/audit-tunjangan-keluarga`), uji silang dobel tunjangan anak (2%+2%), pasangan saling menunjang (10%+10%), kelebihan kuota anak (>2 anak), fitur tindak lanjut pencatatan bukti STS Kasda, dan paginasi modern.
- **v2.8.0 (02 Oktober 2026)**: Integrasi DBF Riwayat Keluarga (`KEL`), atribut finansial master pegawai (NIK, No. Rekening, Bank Penyalur), modul Trace Penggajian Personal (`/laporan/trace-gaji`), penambahan status pegawai pada Unmatched NIP, dan optimasi sinkronisasi database.
- **v2.7.0 (29 September 2026)**: Modul Rekonsiliasi IWP & BPJS Kesehatan (`/laporan/iwp-jamkes`), sistem 2 Tab Master SKPD Induk vs UPTD, dan cetak PDF resmi.
- **v2.6.0 (29 September 2026)**: Sistem Autentikasi Pengguna (`/login`, `/setting/users`) dan Containerisasi Docker VPS (PHP 8.4-FPM, Nginx, MySQL 8).
- **v2.5.0 (09 September 2026)**: Modul Penyelarasan SKPD & UPTD SIMGAJI vs SIMPEG (`/laporan/penyelarasan-unit-kerja`).
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
