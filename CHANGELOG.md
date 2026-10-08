# Catatan Rilis & Progres Pembaruan Aplikasi KONBELPEG

Dokumen ini mencatat seluruh riwayat pembaruan, evolusi fitur, perbaikan bug, dan progres pengembangan sistem **KONBELPEG (Rekonsiliasi Realisasi Belanja Pegawai & SIMGAJI)**.

## 📌 [v2.12.0] - 2026-10-08
### 📋 Modul Laporan Data BNBA (By Name By Address) Perbaikan SKPD SIMGAJI Taspen
- **Modul Baru Laporan BNBA Perbaikan SIMGAJI (`/laporan/perbaikan-simgaji-skpd`)**:
  - Menyajikan daftar nominatif perorangan (By Name By Address) bagi ASN yang memerlukan perbaikan kode atau nama SKPD dan penempatan Satker di aplikasi penggajian SIMGAJI Taspen.
  - Menetapkan data master **SIMPEG / KONBELPEG** sebagai acuan tunggal dasar kebenaran (*single source of truth*) penempatan unit kerja dan jabatan pegawai Pemerintah Daerah.
- **Klasifikasi Selisih & Rekomendasi Tindakan Otomatis**:
  - 🚨 **Beda SKPD Induk (Mutasi Pegawai)**: Mendeteksi perpindahan SKPD pegawai (misal: tercatat di Setda pada SIMGAJI, namun aktif di Dinkes/Dishub pada SIMPEG). Memberikan rekomendasi mutasi kode SKPD SIMGAJI secara otomatis.
  - 📍 **Beda Cabang Disdik (Kabupaten/Kota)**: Mendeteksi ketidaksesuaian penempatan cabang wilayah Dinas Pendidikan SIMGAJI (kode 070-082) terhadap unit kerja/sekolah riil di SIMPEG.
  - 🏢 **Beda UPTD / Satker**: Mendeteksi penempatan satker/UPTD yang keliru pada SKPD yang sama (misal di Dinkes: tercatat di BKOM padahal seharusnya di Instalasi Farmasi).
  - 💡 **Rekomendasi Tindakan Lengkap**: Menyajikan instruksi aksi terperinci: Kode SKPD SIMGAJI Seharusnya, Nama SKPD Resmi, dan UPTD/Sekolah penempatan resmi.
- **Navigasi & Interaktivitas Antarmuka**:
  - Penambahan submenu navigasi resmi `15. BNBA Perbaikan SKPD SIMGAJI` pada kelompok menu sidebar Laporan & Realisasi.
  - Kartu navigasi baru di *Pusat Laporan* (`/laporan`).
  - Tombol pintas navigasi di Tab 4 halaman *Penyelarasan Unit Kerja* (`/laporan/penyelarasan-unit-kerja`).
  - Fitur salin NIP cepat 1-klik (*copy to clipboard*) dengan feedback animasi toast.
  - Filter interaktif berdasarkan Kategori Selisih, SKPD Resmi SIMPEG, SKPD Asal SIMGAJI, dan pencarian instan (NIP, Nama, Jabatan).
- **Format Ekspor Dokumen Resmi**:
  - **Ekspor Excel (.xlsx)**: Diformat khusus sebagai lampiran resmi Berita Acara Usulan Perbaikan Data Penggajian ke PT Taspen / Bank Persepsi (auto-width, bordered, warna zona identitas, dan rekomendasi tindakan).
  - **Ekspor Dokumen PDF (A4 Landscape)**: Format siap cetak dengan kop resmi Pemerintah Daerah / Badan Keuangan dan Aset Daerah serta kolom tanda tangan pejabat penatausahaan keuangan.
- **Pengujian Otomatis (*Feature Tests*)**:
  - Rangkaian pengujian otomatis pada `tests/Feature/LaporanBnbaPerbaikanSimgajiTest.php` (5 tests lulus, 13 asersi, total suite 75 tests lulus).

---

## 📌 [v2.11.0] - 2026-10-07
### 🛡️ Keamanan Sistem, Proteksi Anti-Bot, Mitigasi Serangan & Hardening Halaman Login
- **Proteksi Anti-Bot Cerdas (Honeypot & Cloudflare Turnstile)**:
  - **Honeypot Trap**: Memasang bidang verifikasi tersembunyi (`system_verify_token`) pada formulir masuk. Bot otomatis yang memindai formulir dan mengisinya akan langsung ditolak dan diblokir sementara tanpa mengganggu kenyamanan pegawai ASN (100% transparan tanpa teka-teki gambar).
  - **Dukungan Cloudflare Turnstile**: Integrasi verifikasi captcha modern berbasis Cloudflare Turnstile secara *plug-and-play* melalui `.env` (`TURNSTILE_SITE_KEY` & `TURNSTILE_SECRET_KEY`) sebagai alternatif reCAPTCHA yang ringan dan privasi terjamin.
- **Lapisan Keamanan Autentikasi & Brute-Force Protection**:
  - **Rate Limiting Ganda pada Auth**: Penguncian otomatis (*lockout*) 60 detik setelah 5 kali gagal memasukkan kata sandi per kombinasi akun & IP, serta pembatasan global maksimal 15 kali percobaan per menit per alamat IP di `AuthController.php`.
  - **Route Throttling**: Pembatasan frekuensi akses formulir login (`throttle:20,1` pada rute `POST /login` dan `throttle:60,1` pada rute `GET /login`) untuk meredam serangan bot credential stuffing.
  - **Security Headers Middleware**: Menambahkan middleware global `SecurityHeaders` yang menyuntikkan header keamanan standar industri: `X-Frame-Options: SAMEORIGIN` (anti-clickjacking), `X-Content-Type-Options: nosniff` (anti-MIME sniffing), `X-XSS-Protection: 1; mode=block`, `Referrer-Policy: strict-origin-when-cross-origin`, dan `Permissions-Policy`.
- **Pembaruan Visual Institusional Halaman Login (`/login`)**:
  - Tampilan visual formal dan profesional berstandar korporat/pemerintahan: menghapus elemen orbs warna-warni dan kartu kredensial bawaan/default yang sebelumnya tampil di halaman masuk.
  - Menyelaraskan teks identitas menjadi netral institusional Pemerintah Daerah (Badan Keuangan & Aset Daerah).
- **Pengujian Otomatis (*Feature Tests*)**:
  - Penambahan skenario uji pada `tests/Feature/AuthTest.php` untuk memvalidasi keberadaan field honeypot serta penolakan bot secara otomatis (70 tests lulus, 298 asersi).

---

## 📌 [v2.10.0] - 2026-10-07
### 📤 Modul Upload & Sinkronisasi Master Pegawai SIMPEG via Web Spreadsheet
- **Manajemen & Unggah Berkas SIMPEG (`/master/pegawai-simpeg`)**:
  - Halaman antarmuka khusus untuk mengunggah berkas Excel (`.xlsx`, `.xls`, `.csv`) master kepegawaian SIMPEG BKD secara berkala.
  - Kartu KPI statistik: Total Pegawai terdaftar (rincian PNS, PPPK, Paruh Waktu), Unit Kerja/SKPD terdaftar, Jabatan terdaftar, dan riwayat berkas aktif.
  - Fitur unduh berkas template contoh (`/master/pegawai-simpeg/template`) yang telah terformat dengan gaya resmi dan baris contoh (PNS & PPPK).
- **Metode Impor Cerdas (Upsert & Preservasi Data Finansial)**:
  - Otomatis menambahkan pegawai baru yang belum terdaftar di database lengkap dengan relasi SKPD, UPTD, dan Jabatannya.
  - Memperbarui mutasi SKPD, perubahan unit kerja/UPTD/satker, perubahan jabatan, atau kenaikan pangkat untuk pegawai lama tanpa menghapus data finansial SIMGAJI (NIK, No. Rekening, Bank Penyalur, dan data tanggungan keluarga).
  - Pilihan metode: *Upsert (Perbarui & Tambah - Direkomendasikan)* atau *Insert Only (Hanya Tambah Baru)*.
- **Integrasi Antarmuka & Navigasi**:
  - Submenu baru `4. Upload Pegawai SIMPEG` pada kelompok Master Data sidebar navigasi.
  - Tombol pintas `Upload Excel SIMPEG` pada header halaman Daftar Pegawai (`/pegawai`).
  - Progress bar interaktif (*real-time*) dengan polling cache sistem (`/upload/progress?id=...`) terintegrasi modal SweetAlert2.
  - Tabel riwayat berkas SIMPEG di server dengan kemampuan impor/sinkronisasi ulang (*Re-sync*) dan penghapusan arsip.
- **Optimasi Reverse Proxy & Cloudflare SSL**:
  - Penambahan `$middleware->trustProxies(at: '*');` pada `bootstrap/app.php` untuk memastikan deteksi protokol HTTPS dan penanganan header `X-Forwarded-Proto` dari Cloudflare berjalan tanpa redirect loop.
- **Pengujian Otomatis (*Feature Tests*)**:
  - Pengujian komprehensif pada `tests/Feature/PegawaiSimpegTest.php` (4 skenario pengujian, 13 asersi lulus).

---

## 📌 [v2.9.0] - 2026-10-03
### 🛡️ Modul Audit Tunjangan Keluarga SIMGAJI & Penyelesaian Bukti STS (Surat Tanda Setoran)
- **Modul Audit Tunjangan Keluarga (`/laporan/audit-tunjangan-keluarga`)**:
  - Sistem pengawasan otomatis berbasis rekonsiliasi berkas SIMGAJI Taspen (`KEL_*.DBF` dan `MST_PGW.DBF`) untuk mengidentifikasi potensi kelebihan bayar tunjangan keluarga.
  - Tiga pilar uji silang (*Cross-Audit Rules*):
    1. 👶 **Tab 1: Dobel Tunjangan Anak (2% + 2%)**: Mendeteksi anak yang sama yang diklaim tertunjang sekaligus oleh ayah dan ibu yang keduanya berstatus ASN di Pemerintah Provinsi Kalimantan Selatan.
    2. 👥 **Tab 2: Pasangan Saling Menunjang (10% + 10%)**: Mendeteksi suami dan istri yang sama-sama berstatus ASN Pemprov Kalsel yang saling mendaftarkan pasangannya dan keduanya menerima tunjangan keluarga 10%.
    3. ⚠️ **Tab 3: Melebihi Batas Kuota Anak (>2 Anak Tertunjang)**: Mendeteksi ASN yang memiliki lebih dari 2 anak dengan status tunjangan tertunjang (`kdtunjang = 2`) di sistem SIMGAJI melebihi kuota aturan penggajian.
  - Tautan langsung ke modul *Trace Penggajian Pegawai* (`/laporan/trace-gaji`) untuk setiap ASN yang terlibat guna verifikasi histori transaksi riil.
- **Pencatatan Tindak Lanjut & Bukti Pengembalian Kasda via STS**:
  - Migrasi basis data `audit_tunjangan_resolusis` untuk mencatat penyelesaian temuan audit.
  - Modal interaktif pencatatan tindak lanjut:
    - Status Penyelesaian (*Selesai* vs *Pending / Belum Selesai*).
    - Nomor Bukti STS resmi (*Surat Tanda Setoran ke Rekening Kas Umum Daerah*).
    - Tanggal Setoran STS.
    - Nominal Pengembalian (Rp) yang disetor ke Kasda.
    - Catatan tindak lanjut auditor / pemeriksa.
  - Opsi pembatalan status (*Revert*) jika terdapat kesalahan input atau pembatalan verifikasi.
- **Filter Status & Rekapitulasi Keuangan Terpadu**:
  - Dropdown filter status kasus: *Semua Status*, *Pending (Belum Selesai)*, dan *Sudah Selesai (STS)* sehingga auditor dapat fokus menuntaskan kasus-kasus yang tersisa.
  - Kartu KPI Eksekutif: Total Kasus Temuan, Status Pending, Status Selesai, dan Akumulasi Nominal Setoran STS yang telah kembali ke Kasda.
- **Ekspor Dokumen Resmi**:
  - Ekspor hasil audit komprehensif ke **Microsoft Excel (.xlsx)** dengan format akuntansi dan kolom status STS.
  - Ekspor ke **Dokumen PDF Resmi** (A4 Landscape) siap cetak dan arsip tindak lanjut pengawasan.
- **Komponen Paginasi Modern**:
  - Desain kontrol navigasi halaman kustom (*page pills*, *first/last page*, *prev/next*, *dots* `...`, dan *indicator pill*).
  - Preservasi query string URL (`tab`, `q`, `skpd`, `status`) saat berpindah halaman.
  - Kompatibel penuh dengan tema Light Mode & Dark Mode.
- **Pengujian Otomatis (*Feature Tests*)**:
  - Rangkaian pengujian komprehensif di `tests/Feature/AuditTunjanganKeluargaTest.php` (8 skenario pengujian, 34 asersi lolos).

---

## 📌 [v2.8.0] - 2026-10-02
### 👨‍👩‍👧‍👦 Integrasi Riwayat Keluarga SIMGAJI (KEL), Atribut Finansial Pegawai, Trace Gaji & Peningkatan Unmatched NIP
- **Dukungan Berkas DBF Riwayat Anggota Keluarga & Tanggungan (`KEL_*.DBF`)**:
  - Dukungan berkas DBF ke-3 SIMGAJI: `KEL` (Riwayat Anggota Keluarga & Tanggungan dari SIMGAJI Taspen, 70.000+ data).
  - Migrasi skema basis data terindeks `simgaji_keluargas` (`nip`, `nmkel`, `kdhubkel`, `hubungan`, `kdjenkel`, `jenis_kelamin`, `tgllhr`, `kdtunjang`, `status_tunjangan`, `kdstawin`, `nipsuamiis`, `pekerjaan`, `nosks`, `tglsks`, `tglnikah`, `tglcerai`, `tglwafat`).
  - Pemetaan relasi keluarga otomatis: Suami/Istri (10/20), Anak (11/12/13/21/22...), dan status tunjangan (`kdtunjang` 2 = Tertunjang, 1 = Tidak Tertunjang).
  - Performa query relasional ultra-cepat (<1 ms) untuk lookup keluarga per NIP.
  - Perintah konsol Artisan `php artisan simgaji:sync {--type=all|master|keluarga}` untuk sinkronisasi massal berkas DBF langsung ke basis data.
  - Pembaruan antarmuka Manajemen DBF (`/master/simgaji-dbf`): Kartu status ke-3 untuk berkas KEL, pendeteksi otomatis tipe berkas KEL saat diunggah, tombol *Sinkronkan ke Database*, serta riwayat aktivasi berkas.
- **Atribut Finansial & Identitas Master Pegawai (`pegawais`)**:
  - Penambahan kolom identitas finansial: `nik` (No. KTP), `no_rekening`, `nama_bank`, `npwp`, dan `no_karpeg` pada master pegawai.
  - Sinkronisasi otomatis data perbankan dan kependudukan dari berkas `MST_PGW.DBF` (`noktp`, `norek`, `induk_bank`, `npwp`, `nokarpeg`).
  - Tampilan kolom NIK dan Rekening/Bank pada tabel daftar pegawai (`/pegawai`).
  - Modal interaktif "Detail Pegawai & SIMGAJI" di `/pegawai`: menampilkan data identitas BKD, informasi perbankan SIMGAJI, serta daftar anggota keluarga/tanggungan.
  - Pencarian cerdas di tabel pegawai berdasarkan NIK selain NIP dan Nama.
- **Modul Trace Riwayat Penggajian Pegawai (`/laporan/trace-gaji`)**:
  - Penelusuran riwayat penggajian personal interaktif berdasarkan NIP maupun Nama.
  - Kartu profil finansial lengkap dengan NIK, No. Rekening, Bank Penyalur, NPWP, dan No. Karpeg.
  - Bagian khusus "Daftar Anggota Keluarga & Tanggungan (SIMGAJI Taspen)" yang menyajikan status tertunjang/tidak tertunjang dan usia anggota keluarga.
  - Tabel riwayat transaksi penggajian bulanan (Gaji Pokok, Tunjangan Keluarga, Tunjangan Jabatan, Bruto, Potongan, Netto).
  - Penyempurnaan tampilan responsif serta dukungan Dark Mode & Light Mode dengan kontras optimal.
- **Peningkatan Laporan Unmatched NIP (`/laporan/unmatched-nip`)**:
  - Penambahan kolom status kepegawaian (PNS / PPPK / Non-ASN) dan indikator keberadaan NIP di basis data SIMGAJI.
  - Dropdown filter status pegawai untuk mempermudah identifikasi dan audit transaksi penggajian tak bertuan.
- **Kriteria Transaksi Gaji & Periode Ganda TPP**:
  - Penambahan kolom `jenis_gaji` pada tabel `realisasi_gajis` (Gaji Induk, Gaji Terusan, Gaji Susulan, Kekurangan Gaji, Gaji-13, THR).
  - Penanganan periode ganda TPP: `bulan_kinerja` dan `periode_kas` pada tabel `realisasi_tpps`.
- **Pengujian Otomatis**:
  - Penambahan unit & feature tests: `SimgajiKeluargaTest`, `TraceGajiPegawaiTest`, `UnmatchedNipTest`, `RealisasiGajiKriteriaTest`, `RealisasiTppDualFieldTest`.

---

## 📌 [v2.7.0] - 2026-09-29
### 🩺 Modul Laporan Rekonsiliasi IWP & BPJS Kesehatan (Jamkes)
- **Modul Baru Laporan IWP & Jamkes (`/laporan/iwp-jamkes`)**:
  - Rekonsiliasi komprehensif Iuran Wajib Pegawai (IWP 10%) dan pemotongan Jaminan Kesehatan (BPJS Kesehatan) dari Gaji Reguler (2%) dan TPP (1%).
  - Menyandingkan nilai **IWP 2% Jamkes Gaji (SIMGAJI DBF `piwp2`)**, **IWP 8% Pensiun & THT Taspen (`piwp8`)**, dan **IWP 1% Jamkes TPP (`realisasi_tpps.iuran_iwp`)**.
  - Dilengkapi 5 KPI Eksekutif: Total Iuran Jamkes Gabungan (Gaji 2% + TPP 1%), IWP 2% Jamkes Gaji, IWP 1% Jamkes TPP, IWP 8% Taspen, dan Total Seluruh IWP.
  - Dua mode/tab analitis:
    1. 📊 **Tab Rekapitulasi per SKPD**: Rincian pemotongan per unit kerja/SKPD beserta total pegawai bergaji & penerima TPP.
    2. 👥 **Tab Rincian per Pegawai**: Menampilkan data presisi per pegawai dengan fitur pencarian NIP/Nama, filter SKPD, dan paginasi.
- **Ekspor Dokumen Resmi**:
  - Ekspor ke **Microsoft Excel (.xlsx)** dengan format angka ribuan, styling resmi, dan kalkulasi total otomatis.
  - Ekspor ke **Dokumen PDF Resmi** (A4 Landscape) siap cetak dan arsip rekonsiliasi ke BPJS Kesehatan.
- **Fitur Dua Tab & Cetak Master Data Unit Kerja (SKPD - `/master/skpd`)**:
  - **Sistem 2 Tab Navigasi**:
    1. 🏢 **Tab 1: Ringkasan 42 SKPD Induk**: Menyajikan daftar bersih 42 dinas/badan induk murni, jumlah UPT di bawahnya, jumlah Satker, total pegawai, dan tombol *Lihat UPT*.
    2. 🌿 **Tab 2: Rincian 1.638 Unit Kerja (UPT / Satker)**: Menampilkan data detail UPT/sekolah/satker dengan **Dropdown Filter SKPD Induk** sehingga tidak tertumpuk ratusan sekolah Dinas Pendidikan.
  - Penambahan tombol **Cetak PDF** resmi dan **Ekspor Excel (.xlsx)** yang dinamis menyesuaikan tab yang sedang aktif.
  - Template PDF resmi (`master/skpd_pdf.blade.php`) lengkap dengan kop instansi, rekap jumlah pegawai per unit kerja (`pegawais_count`), serta total pegawai keseluruhan.
  - Pengujian otomatis (*Feature Tests*): `tests/Feature/MasterSkpdPrintTest.php` (4 pengujian lolos).

---

## 📌 [v2.6.0] - 2026-09-29
### 🔐 Sistem Autentikasi Pengguna & Containerisasi Docker untuk VPS
- **Sistem Autentikasi & Keamanan (Login / Logout)**:
  - Implementasi `AuthController` dengan validasi ketat dan proteksi *Rate Limiting* (anti brute-force).
  - Halaman login modern (`/login`) dengan palet LUNO Admin, dukungan Dark/Light mode, Phosphor icons, toggle lihat kata sandi, dan tombol *Isi Otomatis* untuk kemudahan development.
  - Pengamanan seluruh rute aplikasi (`/dashboard`, `/master/*`, `/pegawai`, `/realisasi/*`, `/laporan/*`, `/setting/*`) menggunakan middleware `auth`.
  - Profil pengguna dinamis pada topbar layout dengan menu dropdown dan tombol *Logout* terproteksi CSRF.
  - Seeder akun default (`UserSeeder`): `admin@pemda.go.id` / `password`.
  - Pengujian otomatis (*Feature Tests*): `tests/Feature/AuthTest.php` (8 pengujian lolos, 22 assertions).
- **Infrastruktur Docker & Deployment VPS**:
  - `Dockerfile` multi-stage berbasis PHP 8.4-FPM Bookworm lengkap dengan ekstensi `gd`, `zip`, `pdo_mysql`, `mbstring`, `opcache`, dan `pcntl`.
  - `docker-compose.yml` terintegrasi dengan 3 layanan: PHP-FPM (`app`), Web Server Nginx (`webserver`), dan MySQL 8.0 (`db`) dengan volume persisten.
  - Konfigurasi Nginx (`docker/nginx/default.conf`) dan PHP (`docker/php/local.ini`) dioptimalkan untuk berkas besar: batas upload 100MB (aman untuk DBF SIMGAJI besar seperti `HIS_GPOK.DBF` 42MB) dan timeout 300 detik.
  - Skrip inisialisasi otomatis (`docker/entrypoint.sh`): cek koneksi database, symbolic link storage, auto-migrate, auto-seed admin, dan optimasi cache production.
  - Template konfigurasi `.env.docker.example` dan panduan lengkap `DOCKER_DEPLOYMENT_GUIDE.md`.

---

## 📌 [v2.5.0] - 2026-09-09
### 🏢 Modul Laporan Penyelarasan SKPD — UPTD — SATKER (SIMGAJI vs SIMPEG)
- **Modul Baru Penyelarasan Unit Kerja (`/laporan/penyelarasan-unit-kerja`)**:
  - Menyandingkan dan merekonsiliasi seluruh struktur unit kerja antara database penggajian SIMGAJI (Taspen) dengan master SIMPEG (BKD).
  - Dilengkapi 3 tab analitis terintegrasi:
    1. 🏢 **Rekapitulasi SKPD Induk (Level SKPD)**: Komparasi 57 kode SKPD SIMGAJI vs 42 SKPD SIMPEG beserta perbandingan jumlah pegawai aktif, selisih, dan deteksi 13 kode cabang Dinas Pendidikan (`070` s/d `082`).
    2. 🌿 **Pemetaan UPTD & SATKER (Level Satker)**: Matriks pemetaan 440 kode Satker SIMGAJI (`kdsatker` & `inputer`) terhadap UPTD/Sekolah/Balai di SIMPEG, serta deteksi 25 satker yang menampung multi-UPTD di SIMPEG.
    3. 👥 **Daftar Pegawai Beda Penempatan**: Identifikasi presisi 117 pegawai dengan selisih penempatan (3 pegawai beda SKPD Induk dan 114 pegawai beda UPTD/Sekolah).
- **Fitur Ekspor & Integrasi Cerdas**:
  - Ekspor multi-tab ke **Microsoft Excel (.xlsx)** dan **Dokumen Cetak / PDF resmi**.
  - Sistem caching performa tinggi (< 0.05 detik waktu muat) dan tombol *Refresh Cache*.
  - Integrasi navigasi pada sidebar menu *11. Penyelarasan SKPD & UPTD* dan kartu baru di *Pusat Laporan*.

---

## 📌 [v2.4.2] - 2026-09-04
### 🎯 Klasifikasi Presisi: "Pangkat di SIMGAJI Lebih Tinggi" vs "Belum Diinput di SIMGAJI"
- **Penyesuaian Status & Skoring Hierarki Kepangkatan**:
  - Mengimplementasikan evaluasi hierarki kepangkatan (`getPangkatScore`) untuk membedakan arah selisih pangkat antara Aplikasi vs SIMGAJI:
    1. 🔵 **Pangkat di SIMGAJI Lebih Tinggi (`simgaji_lebih_tinggi` - 85 Pegawai)**:
       - Kasus di mana pegawai sudah resmi naik pangkat di SIMGAJI, namun data di aplikasi master belum diperbarui (contoh: NIP `197004192007012012` - Marsyidah, S.Pd; di Aplikasi masih `III/d`, sedangkan di SIMGAJI sudah `IV/a` / `4A`).
       - Dilengkapi nomor SK kenaikan pangkat (`800.1.3.2/03/BKD/2026`), TMT Gaji (`01/09/2026`), dan Gapok dari database `HIS_GPOK`.
       - Tombol **Update** langsung memutakhirkan pangkat aplikasi ke pangkat mutakhir SIMGAJI.
    2. 🔴 **Belum Diinput di SIMGAJI (`belum_diinput` - 39 Pegawai)**:
       - Kasus di mana pangkat di Aplikasi lebih tinggi dari SIMGAJI (Aplikasi > SIMGAJI), dan belum diproses/dientri oleh operator SIMGAJI.
    3. 🟢 **Sudah Sesuai di SIMGAJI via HIS_GPOK (`sudah_terjadwal` - 351 Pegawai)**:
       - Pegawai dengan SK kenaikan pangkat yang sudah terjadwal/sesuai di riwayat SIMGAJI.
- **Pembaruan Antarmuka & Filter Dropdown**:
  - Filter `status_sk` diperkaya:
    - `⚡ Semua Perlu Penyesuaian (124)`
    - `🔵 Pangkat di SIMGAJI Lebih Tinggi (85)`
    - `🔴 Belum Diinput di SIMGAJI (39)`
    - `🟢 Sudah Sesuai di SIMGAJI via HIS_GPOK (351)`
    - `Semua Riwayat (475)`
  - Widget *Kenaikan Pangkat* menampilkan ringkasan komposisi: `85 SIMGAJI Tinggi • 39 Belum Diinput`.
  - Tombol sinkronisasi massal (*Perbarui Semua Pangkat*) secara aman memprioritaskan pegawai dengan status SIMGAJI Lebih Tinggi agar tidak mendowngrade pangkat pegawai.
- **Pembaruan Ekspor Dokumen**:
  - Format Excel dan cetak PDF kini secara eksplisit mencantumkan status `Pangkat di SIMGAJI Lebih Tinggi` dengan keterangan perbandingan pangkat dan detail SK.

---

## 📌 [v2.4.1] - 2026-09-04
### 🎯 Pangkat Efektif SIMGAJI Mengadopsi HIS_GPOK (124 Selisih Riil)
- **Pengakuan Pangkat SIMGAJI Berdasarkan HIS_GPOK**:
  - Kolom *Pangkat di SIMGAJI* kini secara otomatis mengadopsi pangkat aktif mutakhir dari database `HIS_GPOK` (misal: Ivo Putri Viddy Andini diakui berpangkat **`III/a (3A)`**, bukan lagi `II/d`).
  - Menampilkan referensi master berjalan *Master: II/d (2D)* untuk kejelasan audit.
- **Fokus Antrean Kerja Operator**:
  - Tab *Kenaikan Pangkat* kini secara default memfilter dan menampilkan **124 pegawai** yang benar-benar belum diinput ke SIMGAJI (badge count tab menampilkan 124).
  - Sebanyak **351 pegawai** yang SK-nya sudah selesai dikerjakan di SIMGAJI otomatis berstatus **🟢 Sudah Sesuai di SIMGAJI via HIS_GPOK**.

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
    - Terverifikasi berstatus *Terjadwal di SIMGAJI* dengan Golongan III/a, No. SK `800.1.3.2/02/BKD/2026`, dan TMT Gaji `01/03/2026`.
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
