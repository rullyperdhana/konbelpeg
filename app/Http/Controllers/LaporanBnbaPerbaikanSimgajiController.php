<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use XBase\TableReader;

class LaporanBnbaPerbaikanSimgajiController extends Controller
{
    /**
     * Peta Kode SKPD Standar SIMGAJI Taspen
     */
    private array $skpdCodeMap = [
        '000' => 'SKPD BELUM DITENTUKAN / TRANSIT',
        '001' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '002' => 'DINAS KESEHATAN',
        '003' => 'RUMAH SAKIT DAERAH ULIN BANJARMASIN',
        '004' => 'RUMAH SAKIT JIWA SAMBANG LIHUM',
        '005' => 'RUMAH SAKIT dr. H. MOCH. ANSARI SALEH',
        '006' => 'DINAS PEKERJAAN UMUM DAN PENATAAN RUANG',
        '007' => 'BADAN PERENCANAAN PEMBANGUNAN DAERAH',
        '008' => 'BADAN RISET DAN INOVASI DAERAH',
        '009' => 'DINAS PERHUBUNGAN',
        '010' => 'DINAS LINGKUNGAN HIDUP',
        '011' => 'DINAS ENERGI DAN SUMBER DAYA MINERAL',
        '012' => 'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK DAN KELUARGA BERENCANA',
        '013' => 'DINAS PERKEBUNAN DAN PETERNAKAN',
        '014' => 'DINAS SOSIAL',
        '015' => 'DINAS KOPERASI, USAHA KECIL, DAN MENENGAH',
        '016' => 'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',
        '017' => 'DINAS PARIWISATA',
        '018' => 'DINAS KEPEMUDAAN DAN OLAHRAGA',
        '019' => 'BADAN KESATUAN BANGSA DAN POLITIK',
        '020' => 'DINAS KELAUTAN DAN PERIKANAN',
        '021' => 'DINAS PERTANIAN DAN KETAHANAN PANGAN',
        '022' => 'DINAS KEHUTANAN',
        '023' => 'SATUAN POLISI PAMONG PRAJA DAN PEMADAM KEBAKARAN',
        '024' => 'DINAS PERDAGANGAN',
        '025' => 'BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH',
        '026' => 'DINAS PEMBERDAYAAN MASYARAKAT DAN DESA',
        '027' => 'SEKRETARIAT DAERAH',
        '029' => 'SEKRETARIAT DPRD',
        '030' => 'BADAN PENANGGULANGAN BENCANA DAERAH',
        '031' => 'INSPEKTORAT DAERAH',
        '032' => 'DINAS PERPUSTAKAAN DAN KEARSIPAN',
        '033' => 'BADAN PENDAPATAN DAERAH',
        '034' => 'DINAS TENAGA KERJA DAN TRANSMIGRASI',
        '035' => 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA DAERAH',
        '036' => 'BADAN KEPEGAWAIAN DAERAH',
        '037' => 'BADAN PENGHUBUNG',
        '041' => 'RUMAH SAKIT GIGI DAN MULUT GUSTI HASAN AMAN',
        '042' => 'KEPALA DAERAH / GUBERNUR',
        '043' => 'WAKIL KEPALA DAERAH / WAKIL GUBERNUR',
        '070' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH LAUT)',
        '071' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. KOTABARU)',
        '072' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BANJAR)',
        '073' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BARITO KUALA)',
        '074' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TAPIN)',
        '075' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI SELATAN)',
        '076' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI TENGAH)',
        '077' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI UTARA)',
        '078' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TABALONG)',
        '079' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH BUMBU)',
        '080' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BALANGAN)',
        '081' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KOTA BANJARMASIN)',
        '082' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KOTA BANJARBARU)',
        '099' => 'PEGAWAI TITIPAN / LUAR DAERAH',
        '100' => 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN',
        '101' => 'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
        '102' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
        '103' => 'DINAS PERINDUSTRIAN',
    ];

    /**
     * Peta Deteksi Cabang Wilayah Dinas Pendidikan
     */
    private array $disdikBranchKeywords = [
        'BANJARMASIN' => ['code' => '081', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KOTA BANJARMASIN)'],
        'BANJARBARU' => ['code' => '082', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KOTA BANJARBARU)'],
        'TANAH BUMBU' => ['code' => '079', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH BUMBU)'],
        'BATULICIN' => ['code' => '079', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH BUMBU)'],
        'TANAH LAUT' => ['code' => '070', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH LAUT)'],
        'PELAIHARI' => ['code' => '070', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TANAH LAUT)'],
        'BARITO KUALA' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BARITO KUALA)'],
        'BATOLA' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BARITO KUALA)'],
        'MARABAHAN' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BARITO KUALA)'],
        'HULU SUNGAI SELATAN' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI SELATAN)'],
        'KANDANGAN' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI SELATAN)'],
        'HSS' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI SELATAN)'],
        'HULU SUNGAI TENGAH' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI TENGAH)'],
        'BARABAI' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI TENGAH)'],
        'HST' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI TENGAH)'],
        'HULU SUNGAI UTARA' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI UTARA)'],
        'AMUNTAI' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI UTARA)'],
        'HSU' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. HULU SUNGAI UTARA)'],
        'KOTABARU' => ['code' => '071', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. KOTABARU)'],
        'TABALONG' => ['code' => '078', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TABALONG)'],
        'TANJUNG' => ['code' => '078', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TABALONG)'],
        'BALANGAN' => ['code' => '080', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BALANGAN)'],
        'PARINGIN' => ['code' => '080', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BALANGAN)'],
        'TAPIN' => ['code' => '074', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TAPIN)'],
        'RANTAU' => ['code' => '074', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. TAPIN)'],
        'BANJAR' => ['code' => '072', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BANJAR)'],
        'MARTAPURA' => ['code' => '072', 'nama' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN (KAB. BANJAR)'],
    ];

    /**
     * Dapatkan File DBF Aktif (MST_PGW)
     */
    private function getActiveDbfFile(): ?array
    {
        return app(RekonsiliasiSimgajiController::class)->getActiveDbfFile('mst_pgw');
    }

    /**
     * Mengambil & Mengolah Data BNBA Perbaikan SIMGAJI (Acuan SIMPEG)
     */
    public function getBnbaData(bool $forceRefresh = false): array
    {
        $cacheKey = 'laporan_bnba_perbaikan_simgaji_v3';
        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $activeFile = $this->getActiveDbfFile();
        if (! $activeFile || ! file_exists($activeFile['path'])) {
            return [
                'error' => 'File Master SIMGAJI (MST_PGW) tidak ditemukan. Silakan unggah database melalui menu Upload Master SIMGAJI.',
            ];
        }

        // 1. Ambil data master SIMPEG dari Database
        $dbPegawais = Pegawai::with([
            'unitKerja:id,skpd,upt,satker',
            'jabatan:id,nama',
        ])->get()->keyBy('nip');

        if ($dbPegawais->isEmpty()) {
            return [
                'error' => 'Data master pegawai SIMPEG masih kosong di database. Silakan impor atau sinkronkan data master pegawai SIMPEG terlebih dahulu.',
            ];
        }

        // Peta reverse: Nama SKPD SIMPEG => Kode SKPD SIMGAJI
        $simpegToSimgajiCode = [];
        foreach ($this->skpdCodeMap as $code => $name) {
            $cleanName = trim((string) preg_replace('/\s*\(.*?\)/', '', $name));
            if (! isset($simpegToSimgajiCode[$cleanName])) {
                $simpegToSimgajiCode[$cleanName] = $code;
            }
        }

        // 2. Baca file DBF SIMGAJI (Tahap 1: Pemetaan Satker Dominan & Akumulasi SKPD)
        $table = new TableReader($activeFile['path']);
        $satkerMap = [];
        $rawRecords = [];
        $skpdStatsMap = [];

        while ($record = $table->nextRecord()) {
            if ($record->isDeleted()) {
                continue;
            }

            $nip = trim((string) $record->get('nip'));
            $nama = trim((string) $record->get('nama'));
            $kdskpd = trim((string) $record->get('kdskpd'));
            $kdsatker = trim((string) $record->get('kdsatker'));
            $inputer = trim((string) $record->get('inputer'));
            $tmtstop = trim((string) $record->get('tmtstop'));
            $kdstapeg = trim((string) $record->get('kdstapeg'));

            // Filter pegawai non-aktif / pensiunan lama
            $isPensiun = in_array($kdstapeg, ['22', '23', '24', '27', '28'])
                || (! empty($tmtstop) && strtotime($tmtstop) < strtotime('2026-09-01'));

            if ($isPensiun) {
                continue;
            }

            // Inisialisasi statistik SKPD SIMGAJI
            if (! isset($skpdStatsMap[$kdskpd])) {
                $skpdStatsMap[$kdskpd] = [
                    'kdskpd' => $kdskpd,
                    'nama_simgaji' => $this->skpdCodeMap[$kdskpd] ?? ('KODE '.$kdskpd),
                    'total_pegawai' => 0,
                    'simpeg_skpds' => [],
                    'total_tidak_di_simpeg' => 0,
                    'total_selisih' => 0,
                ];
            }
            $skpdStatsMap[$kdskpd]['total_pegawai']++;

            // Data SIMPEG
            $simpegPgw = $dbPegawais[$nip] ?? null;
            $simpegSkpd = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->skpd) : '-';
            $simpegUpt = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->upt) : '-';
            $simpegSatker = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->satker) : '-';

            if ($simpegPgw && $simpegSkpd !== '-') {
                $skpdStatsMap[$kdskpd]['simpeg_skpds'][$simpegSkpd] = ($skpdStatsMap[$kdskpd]['simpeg_skpds'][$simpegSkpd] ?? 0) + 1;
            } else {
                $skpdStatsMap[$kdskpd]['total_tidak_di_simpeg']++;
            }

            $rawRecords[] = [
                'nip' => $nip,
                'nama' => $nama,
                'kdskpd' => $kdskpd,
                'kdsatker' => $kdsatker,
                'inputer' => $inputer,
                'simpeg_pgw' => $simpegPgw,
                'skpd_simpeg' => $simpegSkpd,
                'upt_simpeg' => $simpegUpt,
                'satker_simpeg' => $simpegSatker,
            ];

            // Akumulasi Satker SIMGAJI
            $satkerKey = $kdskpd.'|'.$kdsatker;
            if (! isset($satkerMap[$satkerKey])) {
                $satkerMap[$satkerKey] = [
                    'simpeg_upts' => [],
                ];
            }

            if ($simpegPgw && $simpegUpt !== '-') {
                $satkerMap[$satkerKey]['simpeg_upts'][$simpegUpt] = ($satkerMap[$satkerKey]['simpeg_upts'][$simpegUpt] ?? 0) + 1;
            }
        }

        // Tentukan UPT Dominan untuk Tiap Satker SIMGAJI
        $satkerDominantUpt = [];
        foreach ($satkerMap as $sKey => $sItem) {
            if (! empty($sItem['simpeg_upts'])) {
                arsort($sItem['simpeg_upts']);
                $satkerDominantUpt[$sKey] = [
                    'dominant_upt' => key($sItem['simpeg_upts']),
                    'dominant_count' => current($sItem['simpeg_upts']),
                    'total_upt_pgw' => array_sum($sItem['simpeg_upts']),
                ];
            }
        }

        // 3. Bangun Data BNBA Lengkap & Data BNBA Perbaikan
        $allBnbaList = [];
        $perbaikanBnbaList = [];

        foreach ($rawRecords as $r) {
            $nip = $r['nip'];
            $nama = $r['nama'];
            $kdskpd = $r['kdskpd'];
            $kdsatker = $r['kdsatker'];
            $inputer = $r['inputer'];
            $simpegPgw = $r['simpeg_pgw'];
            $skpdSimpeg = $r['skpd_simpeg'];
            $uptSimpeg = $r['upt_simpeg'];
            $satkerSimpeg = $r['satker_simpeg'];

            $simgajiSkpdName = $this->skpdCodeMap[$kdskpd] ?? ('KODE '.$kdskpd);
            $cleanSimgajiSkpd = trim((string) preg_replace('/\s*\(.*?\)/', '', $simgajiSkpdName));

            // Jika pegawai belum terdaftar di SIMPEG
            if (! $simpegPgw) {
                $item = [
                    'nip' => $nip,
                    'nama' => $nama,
                    'golru' => '-',
                    'jabatan' => '-',
                    'status_pegawai' => '-',
                    'kdskpd_simgaji' => $kdskpd,
                    'skpd_simgaji' => $simgajiSkpdName,
                    'kdsatker_simgaji' => $kdsatker ?: '-',
                    'inputer_simgaji' => $inputer ?: '-',
                    'skpd_simpeg' => 'TIDAK TERDAFTAR DI SIMPEG',
                    'kdskpd_rekomendasi' => '-',
                    'skpd_rekomendasi' => '-',
                    'upt_simpeg' => '-',
                    'satker_simpeg' => '-',
                    'jenis_selisih' => 'Tidak Terdaftar di SIMPEG',
                    'status_filter' => 'tidak_di_simpeg',
                    'is_perbaikan' => true,
                    'priority' => 4,
                    'rekomendasi' => 'Daftarkan/sinkronkan data pegawai ini ke master SIMPEG / BKD',
                    'keterangan' => "NIP tercatat di SIMGAJI ({$simgajiSkpdName}), tetapi belum ada di database SIMPEG",
                ];

                $allBnbaList[] = $item;
                $perbaikanBnbaList[] = $item;
                $skpdStatsMap[$kdskpd]['total_selisih']++;

                continue;
            }

            $golru = $simpegPgw->golru ?: '-';
            $jabatan = $simpegPgw->jabatan ? trim($simpegPgw->jabatan->nama) : '-';
            $statusPegawai = $simpegPgw->status_pegawai ?: '-';

            // Kasus 1: Beda SKPD Induk (Mutasi Antar-SKPD)
            $isBedaSkpdInduk = strcasecmp($cleanSimgajiSkpd, $skpdSimpeg) !== 0;
            if ($isBedaSkpdInduk) {
                $kdRekomendasi = $simpegToSimgajiCode[$skpdSimpeg] ?? '-';

                $item = [
                    'nip' => $nip,
                    'nama' => $nama,
                    'golru' => $golru,
                    'jabatan' => $jabatan,
                    'status_pegawai' => $statusPegawai,
                    'kdskpd_simgaji' => $kdskpd,
                    'skpd_simgaji' => $simgajiSkpdName,
                    'kdsatker_simgaji' => $kdsatker ?: '-',
                    'inputer_simgaji' => $inputer ?: '-',
                    'skpd_simpeg' => $skpdSimpeg,
                    'kdskpd_rekomendasi' => $kdRekomendasi,
                    'skpd_rekomendasi' => $skpdSimpeg,
                    'upt_simpeg' => $uptSimpeg,
                    'satker_simpeg' => $satkerSimpeg,
                    'jenis_selisih' => 'Beda SKPD Induk',
                    'status_filter' => 'perlu_perbaikan',
                    'is_perbaikan' => true,
                    'priority' => 1,
                    'rekomendasi' => "Pindahkan SKPD di SIMGAJI ke: {$skpdSimpeg}".($kdRekomendasi !== '-' ? " (Kode: {$kdRekomendasi})" : '').($uptSimpeg !== '-' ? " & Satker: {$uptSimpeg}" : ''),
                    'keterangan' => "SIMGAJI: {$simgajiSkpdName} (Kode {$kdskpd}) | SIMPEG Resmi: {$skpdSimpeg}",
                ];

                $allBnbaList[] = $item;
                $perbaikanBnbaList[] = $item;
                $skpdStatsMap[$kdskpd]['total_selisih']++;

                continue;
            }

            // Kasus 2: Beda Cabang Dinas Pendidikan (Kabupaten / Kota)
            $isDisdikBranch = in_array($kdskpd, ['070', '071', '072', '073', '074', '075', '076', '077', '078', '079', '080', '081', '082']);
            if ($isDisdikBranch) {
                $targetDisdik = null;
                $searchTarget = strtoupper($uptSimpeg.' '.$satkerSimpeg);

                foreach ($this->disdikBranchKeywords as $kw => $info) {
                    if (str_contains($searchTarget, $kw)) {
                        $targetDisdik = $info;
                        break;
                    }
                }

                if ($targetDisdik && $targetDisdik['code'] !== $kdskpd) {
                    $item = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'golru' => $golru,
                        'jabatan' => $jabatan,
                        'status_pegawai' => $statusPegawai,
                        'kdskpd_simgaji' => $kdskpd,
                        'skpd_simgaji' => $simgajiSkpdName,
                        'kdsatker_simgaji' => $kdsatker ?: '-',
                        'inputer_simgaji' => $inputer ?: '-',
                        'skpd_simpeg' => $skpdSimpeg,
                        'kdskpd_rekomendasi' => $targetDisdik['code'],
                        'skpd_rekomendasi' => $targetDisdik['nama'],
                        'upt_simpeg' => $uptSimpeg,
                        'satker_simpeg' => $satkerSimpeg,
                        'jenis_selisih' => 'Beda Cabang Disdik',
                        'status_filter' => 'perlu_perbaikan',
                        'is_perbaikan' => true,
                        'priority' => 2,
                        'rekomendasi' => "Pindahkan Cabang Disdik SIMGAJI ke: {$targetDisdik['nama']} (Kode: {$targetDisdik['code']}) & Satker: {$uptSimpeg}",
                        'keterangan' => "SIMGAJI: Cabang {$kdskpd} | Unit Resmi SIMPEG: {$uptSimpeg}",
                    ];

                    $allBnbaList[] = $item;
                    $perbaikanBnbaList[] = $item;
                    $skpdStatsMap[$kdskpd]['total_selisih']++;

                    continue;
                }
            }

            // Kasus 3: Beda UPTD / Sekolah / Satker di SKPD yang Sama
            $sKey = $kdskpd.'|'.$kdsatker;
            $hasUptDiff = false;
            if (isset($satkerDominantUpt[$sKey]) && $satkerDominantUpt[$sKey]['total_upt_pgw'] >= 3) {
                $domUpt = $satkerDominantUpt[$sKey]['dominant_upt'];
                if ($uptSimpeg !== '-' && strcasecmp($uptSimpeg, $domUpt) !== 0) {
                    $kdRekomendasi = $kdskpd;

                    $item = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'golru' => $golru,
                        'jabatan' => $jabatan,
                        'status_pegawai' => $statusPegawai,
                        'kdskpd_simgaji' => $kdskpd,
                        'skpd_simgaji' => $simgajiSkpdName,
                        'kdsatker_simgaji' => $kdsatker ?: '-',
                        'inputer_simgaji' => $inputer ?: '-',
                        'skpd_simpeg' => $skpdSimpeg,
                        'kdskpd_rekomendasi' => $kdRekomendasi,
                        'skpd_rekomendasi' => $skpdSimpeg,
                        'upt_simpeg' => $uptSimpeg,
                        'satker_simpeg' => $satkerSimpeg,
                        'jenis_selisih' => 'Beda UPTD / Satker',
                        'status_filter' => 'perlu_perbaikan',
                        'is_perbaikan' => true,
                        'priority' => 3,
                        'rekomendasi' => "Sesuaikan penempatan Satker SIMGAJI ke: {$uptSimpeg}",
                        'keterangan' => "SIMPEG: {$uptSimpeg} (Satker SIMGAJI saat ini dominan: {$domUpt})",
                    ];

                    $allBnbaList[] = $item;
                    $perbaikanBnbaList[] = $item;
                    $skpdStatsMap[$kdskpd]['total_selisih']++;
                    $hasUptDiff = true;
                }
            }

            if ($hasUptDiff) {
                continue;
            }

            // Kasus 4: Sudah Sesuai (Cocok 100%)
            $item = [
                'nip' => $nip,
                'nama' => $nama,
                'golru' => $golru,
                'jabatan' => $jabatan,
                'status_pegawai' => $statusPegawai,
                'kdskpd_simgaji' => $kdskpd,
                'skpd_simgaji' => $simgajiSkpdName,
                'kdsatker_simgaji' => $kdsatker ?: '-',
                'inputer_simgaji' => $inputer ?: '-',
                'skpd_simpeg' => $skpdSimpeg,
                'kdskpd_rekomendasi' => $kdskpd,
                'skpd_rekomendasi' => $skpdSimpeg,
                'upt_simpeg' => $uptSimpeg,
                'satker_simpeg' => $satkerSimpeg,
                'jenis_selisih' => 'Sesuai',
                'status_filter' => 'sesuai',
                'is_perbaikan' => false,
                'priority' => 5,
                'rekomendasi' => 'Data penempatan SKPD sudah selaras',
                'keterangan' => "SIMGAJI & SIMPEG: {$skpdSimpeg}",
            ];

            $allBnbaList[] = $item;
        }

        // Urutkan data berdasarkan prioritas (Beda SKPD Induk, Beda Cabang Disdik, Beda UPTD, Tidak di SIMPEG, Sesuai), lalu SKPD, lalu Nama
        $sortFn = function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            $cmpSkpd = strcmp($a['skpd_simpeg'], $b['skpd_simpeg']);
            if ($cmpSkpd !== 0) {
                return $cmpSkpd;
            }

            return strcmp($a['nama'], $b['nama']);
        };
        usort($allBnbaList, $sortFn);
        usort($perbaikanBnbaList, $sortFn);

        // 4. Bangun Matriks Pemetaan Master SKPD (57 Kode SIMGAJI)
        ksort($skpdStatsMap);
        $skpdMasterMappings = [];

        foreach ($skpdStatsMap as $kd => $s) {
            $namaSimgaji = $s['nama_simgaji'];
            arsort($s['simpeg_skpds']);
            $dominanSimpeg = ! empty($s['simpeg_skpds']) ? key($s['simpeg_skpds']) : '-';
            $totalPegawai = $s['total_pegawai'];
            $totalSelisih = $s['total_selisih'];
            $totalTidakDiSimpeg = $s['total_tidak_di_simpeg'];
            $totalSesuai = max(0, $totalPegawai - $totalSelisih);

            $isDisdik = in_array($kd, ['070', '071', '072', '073', '074', '075', '076', '077', '078', '079', '080', '081', '082']);

            if ($isDisdik) {
                $statusNama = 'CABANG_DISDIK';
                $rekomendasiNama = "Cabang wilayah Dinas Pendidikan. Selaraskan nama cabang di SIMGAJI: {$namaSimgaji}";
            } elseif ($dominanSimpeg === '-') {
                $statusNama = 'BELUM_TERPETAKAN';
                $rekomendasiNama = 'Belum terpetakan ke data pegawai SIMPEG. Periksa status keaktifan kode di SIMGAJI.';
            } elseif ($totalSelisih > 0) {
                $statusNama = 'ADA_SELISIH';
                $rekomendasiNama = "Standardisasi nama SKPD di SIMGAJI menjadi: {$dominanSimpeg}. Terdapat {$totalSelisih} pegawai perlu disinkronkan.";
            } else {
                $statusNama = 'SESUAI';
                $rekomendasiNama = "Standardisasi nama SKPD di SIMGAJI menjadi: {$dominanSimpeg} (Data sudah selaras).";
            }

            $skpdMasterMappings[] = [
                'kdskpd' => $kd,
                'nama_simgaji' => $namaSimgaji,
                'nama_simpeg_dominan' => $dominanSimpeg,
                'kdskpd_rekomendasi' => $kd,
                'total_pegawai' => $totalPegawai,
                'total_sesuai' => $totalSesuai,
                'total_selisih' => $totalSelisih,
                'total_tidak_di_simpeg' => $totalTidakDiSimpeg,
                'status_nama' => $statusNama,
                'rekomendasi' => $rekomendasiNama,
            ];
        }

        // Rekapitulasi Statistik
        $collPerbaikan = collect($perbaikanBnbaList);
        $totalBedaSkpd = $collPerbaikan->where('jenis_selisih', 'Beda SKPD Induk')->count();
        $totalBedaCabangDisdik = $collPerbaikan->where('jenis_selisih', 'Beda Cabang Disdik')->count();
        $totalBedaUptd = $collPerbaikan->where('jenis_selisih', 'Beda UPTD / Satker')->count();
        $totalTidakDiSimpeg = $collPerbaikan->where('jenis_selisih', 'Tidak Terdaftar di SIMPEG')->count();

        $collAll = collect($allBnbaList);
        $allSimpegSkpds = $collAll->pluck('skpd_simpeg')->unique()->filter(fn ($v) => $v !== 'TIDAK TERDAFTAR DI SIMPEG')->sort()->values()->toArray();
        $allSimgajiSkpds = $collAll->pluck('skpd_simgaji')->unique()->sort()->values()->toArray();

        $result = [
            'all_bnba_items' => $allBnbaList,
            'perbaikan_bnba_items' => $perbaikanBnbaList,
            'skpd_mappings' => $skpdMasterMappings,
            'summary' => [
                'total_aktif_simgaji' => count($allBnbaList),
                'total_perlu_perbaikan' => count($perbaikanBnbaList),
                'total_sesuai' => count($allBnbaList) - count($perbaikanBnbaList),
                'total_beda_skpd' => $totalBedaSkpd,
                'total_beda_cabang_disdik' => $totalBedaCabangDisdik,
                'total_beda_uptd' => $totalBedaUptd,
                'total_tidak_di_simpeg' => $totalTidakDiSimpeg,
                'total_skpd_simgaji' => count($skpdMasterMappings),
                'total_skpd_terdampak' => $collPerbaikan->pluck('skpd_simpeg')->unique()->count(),
            ],
            'filter_options' => [
                'skpd_simpeg' => $allSimpegSkpds,
                'skpd_simgaji' => $allSimgajiSkpds,
            ],
            'active_dbf' => $activeFile,
            'cached_at' => date('d/m/Y H:i').' WITA',
        ];

        Cache::put($cacheKey, $result, 86400);

        return $result;
    }

    /**
     * Tampilan Halaman Laporan BNBA & Pemetaan Master SKPD SIMGAJI
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'bnba'); // 'bnba' atau 'master_skpd'
        $statusFilter = $request->get('status', 'perlu_perbaikan'); // 'perlu_perbaikan', 'semua', 'sesuai', 'tidak_di_simpeg'
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));
        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [25, 50, 100, 250, 500])) {
            $perPage = 25;
        }

        $data = $this->getBnbaData();

        if (isset($data['error'])) {
            return view('laporan.perbaikan_simgaji_skpd.index', [
                'error' => $data['error'],
                'items' => new LengthAwarePaginator([], 0, 25),
                'skpdMappings' => [],
                'summary' => [],
                'filterOptions' => [],
                'tab' => $tab,
                'statusFilter' => $statusFilter,
                'kategori' => $kategori,
                'skpdFilter' => $skpdFilter,
                'simgajiFilter' => $simgajiFilter,
                'search' => $search,
                'perPage' => $perPage,
            ]);
        }

        // Tentukan dataset berdasarkan Status Filter
        if ($statusFilter === 'semua') {
            $items = collect($data['all_bnba_items'] ?? []);
        } elseif ($statusFilter === 'sesuai') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'sesuai');
        } elseif ($statusFilter === 'tidak_di_simpeg') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'tidak_di_simpeg');
        } else {
            // Default: 'perlu_perbaikan' (Hanya yang selisih)
            $items = collect($data['perbaikan_bnba_items'] ?? []);
        }

        // Filter Kategori (Hanya relevan jika di tab bnba)
        if ($kategori !== 'semua') {
            $kategoriMap = [
                'beda_skpd' => 'Beda SKPD Induk',
                'beda_cabang_disdik' => 'Beda Cabang Disdik',
                'beda_uptd' => 'Beda UPTD / Satker',
                'tidak_di_simpeg' => 'Tidak Terdaftar di SIMPEG',
            ];
            $targetLabel = $kategoriMap[$kategori] ?? $kategori;
            $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $targetLabel);
        }

        // Filter SKPD SIMPEG
        if ($skpdFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter);
        }

        // Filter SKPD SIMGAJI
        if ($simgajiFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simgaji'] ?? '') === $simgajiFilter);
        }

        // Filter Search
        if ($search !== '') {
            $sLower = strtolower($search);
            $items = $items->filter(function ($item) use ($sLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['upt_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

        // Pagination
        $page = (int) $request->get('page', 1);
        $paginatedItems = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('laporan.perbaikan_simgaji_skpd.index', [
            'items' => $paginatedItems,
            'skpdMappings' => $data['skpd_mappings'] ?? [],
            'filteredTotal' => $items->count(),
            'summary' => $data['summary'] ?? [],
            'filterOptions' => $data['filter_options'] ?? [],
            'activeDbf' => $data['active_dbf'] ?? null,
            'cachedAt' => $data['cached_at'] ?? null,
            'tab' => $tab,
            'statusFilter' => $statusFilter,
            'kategori' => $kategori,
            'skpdFilter' => $skpdFilter,
            'simgajiFilter' => $simgajiFilter,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Segarkan Cache Data Penyelarasan
     */
    public function refreshCache()
    {
        Cache::forget('laporan_bnba_perbaikan_simgaji_v3');
        Cache::forget('laporan_bnba_perbaikan_simgaji_v2');
        $this->getBnbaData(true);

        return redirect()->back()->with('success', 'Data BNBA & Pemetaan Master SKPD SIMGAJI berhasil dihitung ulang dan diperbarui.');
    }

    /**
     * Ekspor Data BNBA & Pemetaan Master SKPD ke Microsoft Excel (.xlsx) Multi-Sheet
     */
    public function exportExcel(Request $request)
    {
        $scope = $request->get('scope', 'perbaikan'); // 'perbaikan' atau 'full'/'semua'
        $statusFilter = $request->get('status', ($scope === 'full' || $scope === 'semua') ? 'semua' : 'perlu_perbaikan');
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));

        $data = $this->getBnbaData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        // Tentukan dataset BNBA
        if ($scope === 'full' || $scope === 'semua' || $statusFilter === 'semua') {
            $items = collect($data['all_bnba_items'] ?? []);
        } elseif ($statusFilter === 'sesuai') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'sesuai');
        } elseif ($statusFilter === 'tidak_di_simpeg') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'tidak_di_simpeg');
        } else {
            $items = collect($data['perbaikan_bnba_items'] ?? []);
        }

        if ($kategori !== 'semua') {
            $kategoriMap = [
                'beda_skpd' => 'Beda SKPD Induk',
                'beda_cabang_disdik' => 'Beda Cabang Disdik',
                'beda_uptd' => 'Beda UPTD / Satker',
                'tidak_di_simpeg' => 'Tidak Terdaftar di SIMPEG',
            ];
            $targetLabel = $kategoriMap[$kategori] ?? $kategori;
            $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $targetLabel);
        }

        if ($skpdFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter);
        }

        if ($simgajiFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simgaji'] ?? '') === $simgajiFilter);
        }

        if ($search !== '') {
            $sLower = strtolower($search);
            $items = $items->filter(function ($item) use ($sLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['upt_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

        $spreadsheet = new Spreadsheet;

        // ==========================================
        // SHEET 1: PEMETAAN MASTER SKPD (57 SKPD)
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('1. Master SKPD SIMGAJI');

        $sheet1->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet1->setCellValue('A2', 'MATRIKS PEMETAAN MASTER KODE & NAMA SKPD SIMGAJI TASPEN VS SIMPEG (ACUAN RESMI)');
        $sheet1->setCellValue('A3', 'Acuan Master Data Resmi: SIMPEG Pemprov Kalsel | 57 Kode SKPD SIMGAJI | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet1->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet1->getStyle('A1')->getFont()->setSize(13);
        $sheet1->getStyle('A2')->getFont()->setSize(11);
        $sheet1->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $headers1 = [
            'No',
            'Kode SKPD SIMGAJI',
            'Nama SKPD di SIMGAJI (Eksisting)',
            'Nama SKPD Resmi Acuan SIMPEG',
            'Kode Rekomendasi',
            'Total Pegawai SIMGAJI',
            'Pegawai Sesuai',
            'Pegawai Selisih',
            'Belum di SIMPEG',
            'Status Keselarasan',
            'Rekomendasi Standardisasi Nama / Tindakan SIMGAJI',
        ];

        $rowNum1 = 5;
        $colLetter = 'A';
        foreach ($headers1 as $h) {
            $sheet1->setCellValue($colLetter.$rowNum1, $h);
            $colLetter++;
        }
        $lastCol1 = chr(ord('A') + count($headers1) - 1);

        $sheet1->getStyle("A{$rowNum1}:{$lastCol1}{$rowNum1}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'], // Slate Dark
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet1->getRowDimension($rowNum1)->setRowHeight(28);

        $no1 = 1;
        $rowNum1++;
        $tableData1 = [];
        foreach ($data['skpd_mappings'] ?? [] as $m) {
            $tableData1[] = [
                $no1++,
                $m['kdskpd'],
                $m['nama_simgaji'],
                $m['nama_simpeg_dominan'],
                $m['kdskpd_rekomendasi'],
                $m['total_pegawai'],
                $m['total_sesuai'],
                $m['total_selisih'],
                $m['total_tidak_di_simpeg'],
                $m['status_nama'],
                $m['rekomendasi'],
            ];
        }

        $sheet1->fromArray($tableData1, null, "A{$rowNum1}");
        $endRow1 = $rowNum1 + count($tableData1) - 1;

        if ($endRow1 >= $rowNum1) {
            $sheet1->getStyle("A{$rowNum1}:{$lastCol1}{$endRow1}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getStyle("A{$rowNum1}:A{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("B{$rowNum1}:B{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("E{$rowNum1}:E{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("F{$rowNum1}:I{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("J{$rowNum1}:J{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', $lastCol1) as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 2: DATA BNBA PEGAWAI
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2Title = ($scope === 'full' || $scope === 'semua' || $statusFilter === 'semua') ? '2. Full BNBA Pegawai' : '2. BNBA Perbaikan Pegawai';
        $sheet2->setTitle($sheet2Title);

        $sheet2->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet2->setCellValue('A2', 'LAPORAN DATA BNBA (BY NAME BY ADDRESS) PEMETAAN & USULAN PERBAIKAN SKPD SIMGAJI');
        $sheet2->setCellValue('A3', 'Acuan Resmi: SIMPEG / KONBELPEG | Total Data: '.number_format($items->count(), 0, ',', '.').' Pegawai | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet2->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet2->getStyle('A1')->getFont()->setSize(13);
        $sheet2->getStyle('A2')->getFont()->setSize(11);
        $sheet2->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $headers2 = [
            'No',
            'NIP',
            'Nama Pegawai',
            'Gol/Ruang',
            'Status Pegawai',
            'Jabatan Resmi (SIMPEG)',
            'Kode SKPD SIMGAJI',
            'Nama SKPD SIMGAJI (Eksisting)',
            'Kode Satker SIMGAJI',
            'Satker / Inputer SIMGAJI',
            'SKPD Acuan Resmi (SIMPEG)',
            'Kode Rekomendasi SIMGAJI',
            'UPTD / Satker Resmi (SIMPEG)',
            'Status / Jenis Selisih',
            'Rekomendasi Tindakan Perbaikan SIMGAJI',
        ];

        $rowNum2 = 5;
        $colLetter = 'A';
        foreach ($headers2 as $h) {
            $sheet2->setCellValue($colLetter.$rowNum2, $h);
            $colLetter++;
        }
        $lastCol2 = chr(ord('A') + count($headers2) - 1);

        $sheet2->getStyle("A{$rowNum2}:{$lastCol2}{$rowNum2}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Brand Primary Dark Blue
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet2->getRowDimension($rowNum2)->setRowHeight(28);

        $no2 = 1;
        $rowNum2++;
        $tableData2 = [];
        foreach ($items as $row) {
            $tableData2[] = [
                $no2++,
                $row['nip'],
                $row['nama'],
                $row['golru'],
                $row['status_pegawai'],
                $row['jabatan'],
                $row['kdskpd_simgaji'],
                $row['skpd_simgaji'],
                $row['kdsatker_simgaji'],
                $row['inputer_simgaji'],
                $row['skpd_simpeg'],
                $row['kdskpd_rekomendasi'],
                $row['upt_simpeg'],
                $row['jenis_selisih'],
                $row['rekomendasi'],
            ];
        }

        $sheet2->fromArray($tableData2, null, "A{$rowNum2}");
        $endRow2 = $rowNum2 + count($tableData2) - 1;

        if ($endRow2 >= $rowNum2) {
            $sheet2->getStyle("A{$rowNum2}:{$lastCol2}{$endRow2}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet2->getStyle("A{$rowNum2}:A{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("B{$rowNum2}:B{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("D{$rowNum2}:E{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("G{$rowNum2}:G{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("I{$rowNum2}:I{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("L{$rowNum2}:L{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("N{$rowNum2}:N{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', $lastCol2) as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Kembali ke sheet 1 sebagai default tampilan
        $spreadsheet->setActiveSheetIndex(0);

        $suffix = ($scope === 'full' || $scope === 'semua' || $statusFilter === 'semua') ? 'FULL_PEMETAAN' : 'PERBAIKAN';
        $filename = "Pemetaan_SKPD_SIMGAJI_vs_SIMPEG_{$suffix}_".date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Ekspor Data ke Dokumen PDF Resmi (A4 Landscape)
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->get('tab', 'bnba');
        $statusFilter = $request->get('status', 'perlu_perbaikan');
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));

        $data = $this->getBnbaData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        if ($statusFilter === 'semua') {
            $items = collect($data['all_bnba_items'] ?? []);
        } elseif ($statusFilter === 'sesuai') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'sesuai');
        } elseif ($statusFilter === 'tidak_di_simpeg') {
            $items = collect($data['all_bnba_items'] ?? [])->filter(fn ($it) => ($it['status_filter'] ?? '') === 'tidak_di_simpeg');
        } else {
            $items = collect($data['perbaikan_bnba_items'] ?? []);
        }

        if ($kategori !== 'semua') {
            $kategoriMap = [
                'beda_skpd' => 'Beda SKPD Induk',
                'beda_cabang_disdik' => 'Beda Cabang Disdik',
                'beda_uptd' => 'Beda UPTD / Satker',
                'tidak_di_simpeg' => 'Tidak Terdaftar di SIMPEG',
            ];
            $targetLabel = $kategoriMap[$kategori] ?? $kategori;
            $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $targetLabel);
        }

        if ($skpdFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter);
        }

        if ($simgajiFilter !== 'semua') {
            $items = $items->filter(fn ($item) => ($item['skpd_simgaji'] ?? '') === $simgajiFilter);
        }

        if ($search !== '') {
            $sLower = strtolower($search);
            $items = $items->filter(function ($item) use ($sLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['upt_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

        ini_set('memory_limit', '512M');
        $isLimited = false;
        if ($items->count() > 500) {
            $items = $items->take(500);
            $isLimited = true;
        }

        $pdf = Pdf::loadView('laporan.perbaikan_simgaji_skpd.pdf', [
            'items' => $items,
            'skpdMappings' => $data['skpd_mappings'] ?? [],
            'summary' => $data['summary'] ?? [],
            'activeFile' => $data['active_dbf'] ?? [],
            'tab' => $tab,
            'statusFilter' => $statusFilter,
            'kategori' => $kategori,
            'skpdFilter' => $skpdFilter,
            'simgajiFilter' => $simgajiFilter,
            'search' => $search,
            'isLimited' => $isLimited,
            'printedAt' => date('d/m/Y H:i').' WITA',
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('BNBA_Perbaikan_SKPD_SIMGAJI_'.date('Ymd_His').'.pdf');
    }
}
