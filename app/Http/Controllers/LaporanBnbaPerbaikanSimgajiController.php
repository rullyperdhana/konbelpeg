<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use XBase\TableReader;

class LaporanBnbaPerbaikanSimgajiController extends Controller
{
    /**
     * Peta Kode SKPD Standar SIMGAJI Taspen (62 Kode SKPD Resmi Berdasarkan Master SIMGAJI + 000 Transit)
     */
    private array $skpdCodeMap = [
        '000' => 'SKPD BELUM DITENTUKAN / TRANSIT',
        '001' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '002' => 'DINAS KESEHATAN',
        '003' => 'DIREKTUR RSU DAERAH ULIN BANJARMASIN',
        '004' => 'RUMAH SAKIT JIWA SAMBANG LIHUM',
        '005' => 'RUMAH SAKIT UMUM DR.H. ANSARI SALEH',
        '006' => 'DINAS PEKERJAAN UMUM DAN PENATAAN RUANG',
        '007' => 'BADAN PERENCANAAN PEMBANGUNAN DAERAH',
        '008' => 'BADAN RISET DAN INOVASI DAERAH',
        '009' => 'DINAS PERHUBUNGAN',
        '010' => 'DINAS LINGKUNGAN HIDUP',
        '011' => 'DINAS ENERGI DAN SUMBER DAYA MINERAL',
        '012' => 'DPPPAKB',
        '013' => 'DINAS PERKEBUNAN DAN PETERNAKAN',
        '014' => 'DINAS SOSIAL',
        '015' => 'DINAS KOPERASI, USAHA KECIL DAN MENENGAH',
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
        '028' => 'SEKRETARIAT DP KORPRI PROV.KALSEL',
        '029' => 'SEKRETARIAT DPRD',
        '030' => 'BADAN PENANGGULANGAN BENCANA DAERAH',
        '031' => 'INSPEKTORAT DAERAH',
        '032' => 'DINAS PERPUSTAKAAN DAN KEARSIPAN',
        '033' => 'BADAN PENDAPATAN DAERAH',
        '034' => 'DINAS TENAGA KERJA DAN TRANSMIGRASI',
        '035' => 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA DAERAH',
        '036' => 'BADAN KEPEGAWAIAN DAERAH',
        '037' => 'BADAN PENGHUBUNG',
        '038' => 'SEKRETARIAT BADAN KOORDINASI DAN PENYULUHAN',
        '039' => 'BADAN PELAYANAN TERPADU SATU PINTU (BPTSP) PROV. KALSEL',
        '040' => 'SEKRETARIAT KOMISI PENYIARAN INDONESIA DAERAH PROV. KALIMANTAN SELATAN',
        '041' => 'RUMAH SAKIT GIGI DAN MULUT GUSTI HASAN AMAN',
        '042' => 'KEPALA DAERAH',
        '043' => 'WAKIL KEPALA DAERAH',
        '070' => 'DINAS PENDIDIKAN SMA/SMK TANAH LAUT',
        '071' => 'DINAS PENDIDIKAN SMA/SMK KOTABARU',
        '072' => 'DINAS PENDIDIKAN SMA/SMK BANJAR',
        '073' => 'DINAS PENDIDIKAN SMA/SMK BARITO KUALA',
        '074' => 'DINAS PENDIDIKAN SMA/SMK TAPIN',
        '075' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI SELATAN',
        '076' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI TENGAH',
        '077' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI UTARA',
        '078' => 'DINAS PENDIDIKAN SMA/SMK TABALONG',
        '079' => 'DINAS PENDIDIKAN SMA/SMK TANAH BUMBU',
        '080' => 'DINAS PENDIDIKAN SMA/SMK BALANGAN',
        '081' => 'DINAS PENDIDIKAN SMA/SMK BANJARMASIN',
        '082' => 'DINAS PENDIDIKAN SMA/SMK BANJARBARU',
        '099' => 'DINAS PENDIDIKAN PROVINSI KALIMANTAN SELATAN',
        '100' => 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN',
        '101' => 'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
        '102' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
        '103' => 'DINAS PERINDUSTRIAN',
        '104' => 'DINAS PENDIDIKAN GURU SMAN/SMKN/SLB',
    ];

    /**
     * Peta Deteksi Cabang Wilayah Dinas Pendidikan
     */
    private array $disdikBranchKeywords = [
        'BANJARMASIN' => ['code' => '081', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BANJARMASIN'],
        'BANJARBARU' => ['code' => '082', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BANJARBARU'],
        'TANAH BUMBU' => ['code' => '079', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TANAH BUMBU'],
        'BATULICIN' => ['code' => '079', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TANAH BUMBU'],
        'TANAH LAUT' => ['code' => '070', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TANAH LAUT'],
        'PELAIHARI' => ['code' => '070', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TANAH LAUT'],
        'BARITO KUALA' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BARITO KUALA'],
        'BATOLA' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BARITO KUALA'],
        'MARABAHAN' => ['code' => '073', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BARITO KUALA'],
        'HULU SUNGAI SELATAN' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI SELATAN'],
        'KANDANGAN' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI SELATAN'],
        'HSS' => ['code' => '075', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI SELATAN'],
        'HULU SUNGAI TENGAH' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI TENGAH'],
        'BARABAI' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI TENGAH'],
        'HST' => ['code' => '076', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI TENGAH'],
        'HULU SUNGAI UTARA' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI UTARA'],
        'AMUNTAI' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI UTARA'],
        'HSU' => ['code' => '077', 'nama' => 'DINAS PENDIDIKAN SMA/SMK HULU SUNGAI UTARA'],
        'KOTABARU' => ['code' => '071', 'nama' => 'DINAS PENDIDIKAN SMA/SMK KOTABARU'],
        'TABALONG' => ['code' => '078', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TABALONG'],
        'TANJUNG' => ['code' => '078', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TABALONG'],
        'BALANGAN' => ['code' => '080', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BALANGAN'],
        'PARINGIN' => ['code' => '080', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BALANGAN'],
        'TAPIN' => ['code' => '074', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TAPIN'],
        'RANTAU' => ['code' => '074', 'nama' => 'DINAS PENDIDIKAN SMA/SMK TAPIN'],
        'BANJAR' => ['code' => '072', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BANJAR'],
        'MARTAPURA' => ['code' => '072', 'nama' => 'DINAS PENDIDIKAN SMA/SMK BANJAR'],
    ];

    /**
     * Dapatkan File DBF Aktif (MST_PGW)
     */
    private function getActiveDbfFile(): ?array
    {
        return app(RekonsiliasiSimgajiController::class)->getActiveDbfFile('mst_pgw');
    }

    /**
     * Membaca dan Menyimpan Cache Master Kode & Satker SIMGAJI dari File Excel (kode_skpd_satker_simgaji.xlsx)
     *
     * @return array{skpds: array<string, string>, satkers: array<string, array{kdskpd: string, nmskpd: string, kdsatker: string, nmsatker: string}>, satkers_by_skpd: array<string, array<int, array{kdsatker: string, nmsatker: string}>>, total_skpds: int, total_satkers: int}
     */
    public function getSimgajiMasterData(): array
    {
        $filePath = base_path('kode_skpd_satker_simgaji.xlsx');
        $mtime = file_exists($filePath) ? filemtime($filePath) : 0;
        $cacheKey = 'master_simgaji_excel_data_'.$mtime;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $skpds = $this->skpdCodeMap;
        $satkers = [];
        $satkersBySkpd = [];

        if (file_exists($filePath)) {
            try {
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getSheetByName('adm29excel') ?? $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);

                foreach ($rows as $i => $r) {
                    if ($i === 1) {
                        continue;
                    }
                    $kdskpd = trim((string) ($r['A'] ?? ''));
                    $nmskpd = trim((string) ($r['B'] ?? ''));
                    $kdsatker = trim((string) ($r['C'] ?? ''));
                    $nmsatker = trim((string) ($r['D'] ?? ''));

                    if ($kdskpd === '') {
                        continue;
                    }

                    $skpds[$kdskpd] = $nmskpd;

                    if ($kdsatker !== '') {
                        $satkers[$kdsatker] = [
                            'kdskpd' => $kdskpd,
                            'nmskpd' => $nmskpd,
                            'kdsatker' => $kdsatker,
                            'nmsatker' => $nmsatker,
                        ];
                        $satkersBySkpd[$kdskpd][] = [
                            'kdsatker' => $kdsatker,
                            'nmsatker' => $nmsatker,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Gunakan skpdCodeMap bawaan jika terjadi kendala pembacaan
            }
        }

        if (! isset($skpds['000'])) {
            $skpds['000'] = 'SKPD BELUM DITENTUKAN / TRANSIT';
        }

        ksort($skpds);

        $result = [
            'skpds' => $skpds,
            'satkers' => $satkers,
            'satkers_by_skpd' => $satkersBySkpd,
            'total_skpds' => count($skpds),
            'total_satkers' => count($satkers),
        ];

        Cache::put($cacheKey, $result, 86400 * 30);

        return $result;
    }

    /**
     * Dapatkan Peta Lookup NIP => Info SIMGAJI (kdskpd, nmskpd, kdsatker, nmsatker, inputer, is_pensiun, status_simgaji)
     *
     * @return array<string, array{kdskpd: string, nmskpd: string, kdsatker: string, nmsatker: string, inputer: string, is_pensiun: bool, status_simgaji: string}>
     */
    public function getSimgajiPgwLookup(): array
    {
        $activeFile = $this->getActiveDbfFile();
        if (! $activeFile || ! file_exists($activeFile['path'])) {
            return [];
        }

        $mtime = filemtime($activeFile['path']);
        $cacheKey = 'simgaji_pgw_lookup_'.$mtime;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $masterData = $this->getSimgajiMasterData();
        $simgajiSkpds = $masterData['skpds'];
        $simgajiSatkers = $masterData['satkers'];

        $table = new TableReader($activeFile['path']);
        $lookup = [];

        while ($record = $table->nextRecord()) {
            if ($record->isDeleted()) {
                continue;
            }

            $nip = trim((string) $record->get('nip'));
            if ($nip === '') {
                continue;
            }

            $kdskpd = trim((string) $record->get('kdskpd'));
            $kdsatker = trim((string) $record->get('kdsatker'));
            $inputer = trim((string) $record->get('inputer'));
            $tmtstop = trim((string) $record->get('tmtstop'));
            $kdstapeg = trim((string) $record->get('kdstapeg'));

            $isPensiun = in_array($kdstapeg, ['22', '23', '24', '27', '28'])
                || (! empty($tmtstop) && strtotime($tmtstop) < strtotime('2026-09-01'));

            $nmskpd = $simgajiSkpds[$kdskpd] ?? ($kdskpd ? 'KODE '.$kdskpd : '-');
            $satkerInfo = $simgajiSatkers[$kdsatker] ?? null;
            $nmsatker = $satkerInfo ? $satkerInfo['nmsatker'] : ($kdsatker ?: '-');

            $statusSimgaji = $isPensiun ? 'Pensiun / Non-Aktif' : 'Aktif';

            $lookup[$nip] = [
                'kdskpd' => $kdskpd ?: '-',
                'nmskpd' => $nmskpd,
                'kdsatker' => $kdsatker ?: '-',
                'nmsatker' => $nmsatker,
                'inputer' => $inputer ?: '-',
                'is_pensiun' => $isPensiun,
                'status_simgaji' => $statusSimgaji,
            ];
        }

        Cache::put($cacheKey, $lookup, 86400 * 30);

        return $lookup;
    }

    /**
     * Peta Reverse: Nama SKPD Resmi SIMPEG => Kode SKPD SIMGAJI
     */
    private function getSimpegToSimgajiCodeMap(array $skpds): array
    {
        $map = [
            'RUMAH SAKIT DAERAH ULIN BANJARMASIN' => '003',
            'RUMAH SAKIT dr. H. MOCH. ANSARI SALEH' => '005',
            'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK DAN KELUARGA BERENCANA' => '012',
            'DINAS KOPERASI, USAHA KECIL, DAN MENENGAH' => '015',
            'DINAS KOPERASI, USAHA KECIL DAN MENENGAH' => '015',
            'KEPALA DAERAH / GUBERNUR' => '042',
            'WAKIL KEPALA DAERAH / WAKIL GUBERNUR' => '043',
            'DINAS PENDIDIKAN DAN KEBUDAYAAN' => '001',
        ];

        foreach ($skpds as $code => $name) {
            $cleanName = trim((string) preg_replace('/\s*\(.*?\)/', '', $name));
            if (! isset($map[$cleanName])) {
                $map[$cleanName] = $code;
            }
            if (! isset($map[$name])) {
                $map[$name] = $code;
            }
        }

        return $map;
    }

    /**
     * Mencocokkan nama UPTD / Sekolah SIMPEG ke Satker SIMGAJI yang Paling Sesuai
     */
    private function findMatchingSatker(string $kdskpd, ?string $uptName, array $satkersBySkpd): ?array
    {
        if (empty($uptName) || $uptName === '-' || ! isset($satkersBySkpd[$kdskpd])) {
            return null;
        }

        $uptUpper = strtoupper($uptName);
        $uptNorm = preg_replace('/\bSEKOLAH\s+MENENGAH\s+ATAS\b/', 'SMA', $uptUpper);
        $uptNorm = preg_replace('/\bSEKOLAH\s+MENENGAH\s+KEJURUAN\b/', 'SMK', $uptNorm);
        $uptNorm = preg_replace('/\bSEKOLAH\s+LUAR\s+BIASA\b/', 'SLB', $uptNorm);
        $uptNorm = preg_replace('/\bSMAN\b/', 'SMA', $uptNorm);
        $uptNorm = preg_replace('/\bSMKN\b/', 'SMK', $uptNorm);
        $uptNorm = preg_replace('/\bSLBN\b/', 'SLB', $uptNorm);

        $ignore = ['DAN', 'YANG', 'DENGAN', 'UNTUK', 'PADA', 'KAB', 'KABUPATEN', 'KOTA', 'PROV', 'PROVINSI', 'KALSEL', 'NEGERI', 'UPTD'];
        $words = array_filter(preg_split('/[^A-Z0-9]+/', $uptNorm), function ($w) use ($ignore) {
            return strlen($w) >= 2 && ! in_array($w, $ignore);
        });

        $best = null;
        $maxScore = 0;

        foreach ($satkersBySkpd[$kdskpd] as $sat) {
            $satUpper = strtoupper($sat['nmsatker']);
            $satNorm = preg_replace('/\bSEKOLAH\s+MENENGAH\s+ATAS\b/', 'SMA', $satUpper);
            $satNorm = preg_replace('/\bSEKOLAH\s+MENENGAH\s+KEJURUAN\b/', 'SMK', $satNorm);
            $satNorm = preg_replace('/\bSEKOLAH\s+LUAR\s+BIASA\b/', 'SLB', $satNorm);
            $satNorm = preg_replace('/\bSMAN\b/', 'SMA', $satNorm);
            $satNorm = preg_replace('/\bSMKN\b/', 'SMK', $satNorm);
            $satNorm = preg_replace('/\bSLBN\b/', 'SLB', $satNorm);

            $score = 0;
            foreach ($words as $w) {
                $weight = in_array($w, ['SMA', 'SMK', 'SLB']) || is_numeric($w) ? 15 : strlen($w) * 2;
                if (preg_match('/\b'.preg_quote($w, '/').'\b/', $satNorm)) {
                    $score += $weight;
                } elseif (str_contains($satNorm, $w)) {
                    $score += $weight * 0.7;
                }
            }
            similar_text($uptNorm, $satNorm, $sim);
            $totalScore = $score + ($sim * 0.5);

            if ($totalScore > $maxScore) {
                $maxScore = $totalScore;
                $best = $sat;
            }
        }

        return $maxScore >= 20 ? array_merge($best, ['score' => round($maxScore, 1)]) : null;
    }

    /**
     * Mengambil & Mengolah Data BNBA Perbaikan SIMGAJI (Acuan SIMPEG)
     */
    public function getBnbaData(bool $forceRefresh = false): array
    {
        $cacheKey = 'laporan_bnba_perbaikan_simgaji_v4';
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

        // Ambil Data Master SKPD & Satker SIMGAJI dari Excel (kode_skpd_satker_simgaji.xlsx)
        $masterData = $this->getSimgajiMasterData();
        $simgajiSkpds = $masterData['skpds'];
        $simgajiSatkers = $masterData['satkers'];
        $satkersBySkpd = $masterData['satkers_by_skpd'];

        // Peta reverse: Nama SKPD SIMPEG => Kode SKPD SIMGAJI
        $simpegToSimgajiCode = $this->getSimpegToSimgajiCodeMap($simgajiSkpds);

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
                    'nama_simgaji' => $simgajiSkpds[$kdskpd] ?? ('KODE '.$kdskpd),
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

            // Dapatkan nama satker dari master SIMGAJI
            $satkerInfo = $simgajiSatkers[$kdsatker] ?? null;
            $nmsatkerSimgaji = $satkerInfo ? $satkerInfo['nmsatker'] : ($kdsatker ?: '-');

            $rawRecords[] = [
                'nip' => $nip,
                'nama' => $nama,
                'kdskpd' => $kdskpd,
                'kdsatker' => $kdsatker,
                'nmsatker' => $nmsatkerSimgaji,
                'inputer' => $inputer,
                'simpeg_pgw' => $simpegPgw,
                'skpd_simpeg' => $simpegSkpd,
                'upt_simpeg' => $simpegUpt,
                'satker_simpeg' => $simpegSatker,
            ];

            // Akumulasi Satker SIMGAJI
            $satkerKey = $kdsatker ?: ($kdskpd.'|unknown');
            if (! isset($satkerMap[$satkerKey])) {
                $satkerMap[$satkerKey] = [
                    'kdsatker' => $kdsatker,
                    'kdskpd' => $kdskpd,
                    'nmskpd' => $simgajiSkpds[$kdskpd] ?? ('KODE '.$kdskpd),
                    'nmsatker' => $nmsatkerSimgaji,
                    'total_pegawai' => 0,
                    'simpeg_upts' => [],
                ];
            }
            $satkerMap[$satkerKey]['total_pegawai']++;

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
            $nmsatkerSimgaji = $r['nmsatker'];
            $inputer = $r['inputer'];
            $simpegPgw = $r['simpeg_pgw'];
            $skpdSimpeg = $r['skpd_simpeg'];
            $uptSimpeg = $r['upt_simpeg'];
            $satkerSimpeg = $r['satker_simpeg'];

            $simgajiSkpdName = $simgajiSkpds[$kdskpd] ?? ('KODE '.$kdskpd);
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
                    'nmsatker_simgaji' => $nmsatkerSimgaji,
                    'inputer_simgaji' => $inputer ?: '-',
                    'skpd_simpeg' => 'TIDAK TERDAFTAR DI SIMPEG',
                    'kdskpd_rekomendasi' => '-',
                    'skpd_rekomendasi' => '-',
                    'kdsatker_rekomendasi' => '-',
                    'satker_rekomendasi' => '-',
                    'upt_simpeg' => '-',
                    'satker_simpeg' => '-',
                    'jenis_selisih' => 'Tidak Terdaftar di SIMPEG',
                    'status_filter' => 'tidak_di_simpeg',
                    'is_perbaikan' => true,
                    'priority' => 4,
                    'rekomendasi' => 'Daftarkan/sinkronkan data pegawai ini ke master SIMPEG / BKD',
                    'keterangan' => "NIP tercatat di SIMGAJI ({$simgajiSkpdName} - Satker: {$nmsatkerSimgaji}), tetapi belum ada di database SIMPEG",
                ];

                $allBnbaList[] = $item;
                $perbaikanBnbaList[] = $item;
                $skpdStatsMap[$kdskpd]['total_selisih']++;

                continue;
            }

            $golru = $simpegPgw->golru ?: '-';
            $jabatan = $simpegPgw->jabatan ? trim($simpegPgw->jabatan->nama) : '-';
            $statusPegawai = $simpegPgw->status_pegawai ?: '-';

            // Pengecekan Khusus Dinas Pendidikan & Rumah Sakit
            $isDisdikSimpeg = str_contains($skpdSimpeg, 'DINAS PENDIDIKAN');
            $isDisdikSimgaji = in_array($kdskpd, ['001', '070', '071', '072', '073', '074', '075', '076', '077', '078', '079', '080', '081', '082', '099', '104']);

            $isRsUlinMatch = ($kdskpd === '003' && str_contains($skpdSimpeg, 'ULIN'));
            $isRsAnsariMatch = ($kdskpd === '005' && str_contains($skpdSimpeg, 'ANSARI'));
            $isDpppakbMatch = ($kdskpd === '012' && str_contains($skpdSimpeg, 'PEMBERDAYAAN PEREMPUAN'));
            $isDinasKoperasiMatch = ($kdskpd === '015' && str_contains($skpdSimpeg, 'KOPERASI'));
            $isKepalaDaerahMatch = (in_array($kdskpd, ['042', '043']) && str_contains($skpdSimpeg, 'KEPALA DAERAH'));

            // Kasus 1: Beda SKPD Induk (Mutasi Antar-SKPD)
            $isSameSkpd = ($isDisdikSimpeg && $isDisdikSimgaji)
                || $isRsUlinMatch
                || $isRsAnsariMatch
                || $isDpppakbMatch
                || $isDinasKoperasiMatch
                || $isKepalaDaerahMatch
                || (strcasecmp($cleanSimgajiSkpd, $skpdSimpeg) === 0);

            if (! $isSameSkpd) {
                $kdRekomendasi = $simpegToSimgajiCode[$skpdSimpeg] ?? '-';
                $targetSatker = ($kdRekomendasi !== '-') ? $this->findMatchingSatker($kdRekomendasi, $uptSimpeg, $satkersBySkpd) : null;
                $kdSatkerRek = $targetSatker ? $targetSatker['kdsatker'] : '-';
                $satkerRek = $targetSatker ? $targetSatker['nmsatker'] : $uptSimpeg;

                $rekomendasiText = "Pindahkan SKPD di SIMGAJI ke: {$skpdSimpeg}".($kdRekomendasi !== '-' ? " (Kode: {$kdRekomendasi})" : '');
                if ($targetSatker) {
                    $rekomendasiText .= " & Satker: {$targetSatker['nmsatker']} (Kode: {$targetSatker['kdsatker']})";
                } elseif ($uptSimpeg !== '-') {
                    $rekomendasiText .= " & Satker: {$uptSimpeg}";
                }

                $item = [
                    'nip' => $nip,
                    'nama' => $nama,
                    'golru' => $golru,
                    'jabatan' => $jabatan,
                    'status_pegawai' => $statusPegawai,
                    'kdskpd_simgaji' => $kdskpd,
                    'skpd_simgaji' => $simgajiSkpdName,
                    'kdsatker_simgaji' => $kdsatker ?: '-',
                    'nmsatker_simgaji' => $nmsatkerSimgaji,
                    'inputer_simgaji' => $inputer ?: '-',
                    'skpd_simpeg' => $skpdSimpeg,
                    'kdskpd_rekomendasi' => $kdRekomendasi,
                    'skpd_rekomendasi' => $skpdSimpeg,
                    'kdsatker_rekomendasi' => $kdSatkerRek,
                    'satker_rekomendasi' => $satkerRek,
                    'upt_simpeg' => $uptSimpeg,
                    'satker_simpeg' => $satkerSimpeg,
                    'jenis_selisih' => 'Beda SKPD Induk',
                    'status_filter' => 'perlu_perbaikan',
                    'is_perbaikan' => true,
                    'priority' => 1,
                    'rekomendasi' => $rekomendasiText,
                    'keterangan' => "SIMGAJI: {$simgajiSkpdName} (Satker: {$nmsatkerSimgaji}) | SIMPEG Resmi: {$skpdSimpeg}",
                ];

                $allBnbaList[] = $item;
                $perbaikanBnbaList[] = $item;
                $skpdStatsMap[$kdskpd]['total_selisih']++;

                continue;
            }

            // Kasus 2: Beda Cabang Dinas Pendidikan (Kabupaten / Kota)
            if ($isDisdikSimpeg && $isDisdikSimgaji) {
                $targetDisdik = null;
                $searchTarget = strtoupper($uptSimpeg.' '.$satkerSimpeg);

                foreach ($this->disdikBranchKeywords as $kw => $info) {
                    if (str_contains($searchTarget, $kw)) {
                        $targetDisdik = $info;
                        break;
                    }
                }

                if ($targetDisdik && $targetDisdik['code'] !== $kdskpd) {
                    $targetSatker = $this->findMatchingSatker($targetDisdik['code'], $uptSimpeg, $satkersBySkpd);
                    $kdSatkerRek = $targetSatker ? $targetSatker['kdsatker'] : '-';
                    $satkerRek = $targetSatker ? $targetSatker['nmsatker'] : $uptSimpeg;

                    $rekomendasiText = "Pindahkan Cabang Disdik SIMGAJI ke: {$targetDisdik['nama']} (Kode: {$targetDisdik['code']})";
                    if ($targetSatker) {
                        $rekomendasiText .= " & Satker: {$targetSatker['nmsatker']} (Kode: {$targetSatker['kdsatker']})";
                    } elseif ($uptSimpeg !== '-') {
                        $rekomendasiText .= " & Satker: {$uptSimpeg}";
                    }

                    $item = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'golru' => $golru,
                        'jabatan' => $jabatan,
                        'status_pegawai' => $statusPegawai,
                        'kdskpd_simgaji' => $kdskpd,
                        'skpd_simgaji' => $simgajiSkpdName,
                        'kdsatker_simgaji' => $kdsatker ?: '-',
                        'nmsatker_simgaji' => $nmsatkerSimgaji,
                        'inputer_simgaji' => $inputer ?: '-',
                        'skpd_simpeg' => $skpdSimpeg,
                        'kdskpd_rekomendasi' => $targetDisdik['code'],
                        'skpd_rekomendasi' => $targetDisdik['nama'],
                        'kdsatker_rekomendasi' => $kdSatkerRek,
                        'satker_rekomendasi' => $satkerRek,
                        'upt_simpeg' => $uptSimpeg,
                        'satker_simpeg' => $satkerSimpeg,
                        'jenis_selisih' => 'Beda Cabang Disdik',
                        'status_filter' => 'perlu_perbaikan',
                        'is_perbaikan' => true,
                        'priority' => 2,
                        'rekomendasi' => $rekomendasiText,
                        'keterangan' => "SIMGAJI: Cabang {$kdskpd} ({$nmsatkerSimgaji}) | Unit Resmi SIMPEG: {$uptSimpeg}",
                    ];

                    $allBnbaList[] = $item;
                    $perbaikanBnbaList[] = $item;
                    $skpdStatsMap[$kdskpd]['total_selisih']++;

                    continue;
                }
            }

            // Kasus 3: Beda UPTD / Sekolah / Satker di SKPD yang Sama
            $sKey = $kdsatker ?: ($kdskpd.'|unknown');
            $hasUptDiff = false;
            if ($uptSimpeg !== '-' && isset($satkerDominantUpt[$sKey]) && $satkerDominantUpt[$sKey]['total_upt_pgw'] >= 3) {
                $domUpt = $satkerDominantUpt[$sKey]['dominant_upt'];
                if (strcasecmp($uptSimpeg, $domUpt) !== 0) {
                    $targetSatker = $this->findMatchingSatker($kdskpd, $uptSimpeg, $satkersBySkpd);
                    $kdSatkerRek = $targetSatker ? $targetSatker['kdsatker'] : '-';
                    $satkerRek = $targetSatker ? $targetSatker['nmsatker'] : $uptSimpeg;

                    $rekomendasiText = 'Sesuaikan penempatan Satker SIMGAJI ke: '.($targetSatker ? "{$targetSatker['nmsatker']} (Kode: {$targetSatker['kdsatker']})" : $uptSimpeg);

                    $item = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'golru' => $golru,
                        'jabatan' => $jabatan,
                        'status_pegawai' => $statusPegawai,
                        'kdskpd_simgaji' => $kdskpd,
                        'skpd_simgaji' => $simgajiSkpdName,
                        'kdsatker_simgaji' => $kdsatker ?: '-',
                        'nmsatker_simgaji' => $nmsatkerSimgaji,
                        'inputer_simgaji' => $inputer ?: '-',
                        'skpd_simpeg' => $skpdSimpeg,
                        'kdskpd_rekomendasi' => $kdskpd,
                        'skpd_rekomendasi' => $skpdSimpeg,
                        'kdsatker_rekomendasi' => $kdSatkerRek,
                        'satker_rekomendasi' => $satkerRek,
                        'upt_simpeg' => $uptSimpeg,
                        'satker_simpeg' => $satkerSimpeg,
                        'jenis_selisih' => 'Beda UPTD / Satker',
                        'status_filter' => 'perlu_perbaikan',
                        'is_perbaikan' => true,
                        'priority' => 3,
                        'rekomendasi' => $rekomendasiText,
                        'keterangan' => "SIMPEG: {$uptSimpeg} | Satker SIMGAJI: {$nmsatkerSimgaji} (Dominan: {$domUpt})",
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
                'nmsatker_simgaji' => $nmsatkerSimgaji,
                'inputer_simgaji' => $inputer ?: '-',
                'skpd_simpeg' => $skpdSimpeg,
                'kdskpd_rekomendasi' => $kdskpd,
                'skpd_rekomendasi' => $skpdSimpeg,
                'kdsatker_rekomendasi' => $kdsatker ?: '-',
                'satker_rekomendasi' => $nmsatkerSimgaji,
                'upt_simpeg' => $uptSimpeg,
                'satker_simpeg' => $satkerSimpeg,
                'jenis_selisih' => 'Sesuai',
                'status_filter' => 'sesuai',
                'is_perbaikan' => false,
                'priority' => 5,
                'rekomendasi' => 'Data penempatan SKPD & Satker sudah selaras',
                'keterangan' => "SIMGAJI & SIMPEG: {$skpdSimpeg}".($nmsatkerSimgaji !== '-' ? " ({$nmsatkerSimgaji})" : ''),
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

        // 4. Bangun Matriks Pemetaan Master SKPD (62 SKPD SIMGAJI Resmi)
        $skpdMasterMappings = [];

        foreach ($simgajiSkpds as $kd => $namaSimgaji) {
            $s = $skpdStatsMap[$kd] ?? null;
            $totalSatkers = count($satkersBySkpd[$kd] ?? []);

            if ($s) {
                arsort($s['simpeg_skpds']);
                $dominanSimpeg = ! empty($s['simpeg_skpds']) ? key($s['simpeg_skpds']) : '-';
                $totalPegawai = $s['total_pegawai'];
                $totalSelisih = $s['total_selisih'];
                $totalTidakDiSimpeg = $s['total_tidak_di_simpeg'];
                $totalSesuai = max(0, $totalPegawai - $totalSelisih);

                $isDisdik = in_array($kd, ['070', '071', '072', '073', '074', '075', '076', '077', '078', '079', '080', '081', '082', '099', '104']);

                if ($isDisdik) {
                    $statusNama = 'CABANG_DISDIK';
                    $rekomendasiNama = "Cabang/Unit wilayah Dinas Pendidikan ({$totalSatkers} Satker). Selaraskan nama cabang di SIMGAJI: {$namaSimgaji}";
                } elseif ($dominanSimpeg === '-') {
                    $statusNama = 'BELUM_TERPETAKAN';
                    $rekomendasiNama = "Belum terpetakan ke data pegawai SIMPEG ({$totalSatkers} Satker). Periksa status keaktifan kode di SIMGAJI.";
                } elseif ($totalSelisih > 0) {
                    $statusNama = 'ADA_SELISIH';
                    $rekomendasiNama = "Standardisasi nama SKPD di SIMGAJI menjadi: {$dominanSimpeg} ({$totalSatkers} Satker). Terdapat {$totalSelisih} pegawai perlu disinkronkan.";
                } else {
                    $statusNama = 'SESUAI';
                    $rekomendasiNama = "Standardisasi nama SKPD di SIMGAJI menjadi: {$dominanSimpeg} ({$totalSatkers} Satker). Data sudah selaras.";
                }
            } else {
                $dominanSimpeg = '-';
                $totalPegawai = 0;
                $totalSelisih = 0;
                $totalTidakDiSimpeg = 0;
                $totalSesuai = 0;
                $statusNama = 'NON_AKTIF';
                $rekomendasiNama = "Kode SKPD terdaftar di Master SIMGAJI ({$totalSatkers} Satker), saat ini tidak ada pegawai aktif pada file DBF.";
            }

            $skpdMasterMappings[] = [
                'kdskpd' => $kd,
                'nama_simgaji' => $namaSimgaji,
                'nama_simpeg_dominan' => $dominanSimpeg,
                'kdskpd_rekomendasi' => $kd,
                'total_satker' => $totalSatkers,
                'total_pegawai' => $totalPegawai,
                'total_sesuai' => $totalSesuai,
                'total_selisih' => $totalSelisih,
                'total_tidak_di_simpeg' => $totalTidakDiSimpeg,
                'status_nama' => $statusNama,
                'rekomendasi' => $rekomendasiNama,
            ];
        }

        // 5. Bangun Matriks Master Satker SIMGAJI (551 Satker)
        $satkerMasterMappings = [];
        foreach ($simgajiSatkers as $kds => $satInfo) {
            $kd = $satInfo['kdskpd'];
            $sStats = $satkerMap[$kds] ?? null;
            $totalPgw = $sStats['total_pegawai'] ?? 0;
            $domUpt = $satkerDominantUpt[$kds]['dominant_upt'] ?? '-';

            $satkerMasterMappings[] = [
                'kdskpd' => $kd,
                'nmskpd' => $satInfo['nmskpd'],
                'kdsatker' => $kds,
                'nmsatker' => $satInfo['nmsatker'],
                'total_pegawai' => $totalPgw,
                'dominan_simpeg_upt' => $domUpt,
                'status' => $totalPgw > 0 ? 'AKTIF' : '0_PEGAWAI',
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
            'satker_mappings' => $satkerMasterMappings,
            'summary' => [
                'total_aktif_simgaji' => count($allBnbaList),
                'total_perlu_perbaikan' => count($perbaikanBnbaList),
                'total_sesuai' => count($allBnbaList) - count($perbaikanBnbaList),
                'total_beda_skpd' => $totalBedaSkpd,
                'total_beda_cabang_disdik' => $totalBedaCabangDisdik,
                'total_beda_uptd' => $totalBedaUptd,
                'total_tidak_di_simpeg' => $totalTidakDiSimpeg,
                'total_skpd_simgaji' => count($skpdMasterMappings),
                'total_satker_simgaji' => count($satkerMasterMappings),
                'total_satker_aktif' => collect($satkerMasterMappings)->where('total_pegawai', '>', 0)->count(),
                'total_skpd_terdampak' => $collPerbaikan->pluck('skpd_simpeg')->unique()->count(),
            ],
            'filter_options' => [
                'skpd_simpeg' => $allSimpegSkpds,
                'skpd_simgaji' => $allSimgajiSkpds,
                'all_master_skpds' => $simgajiSkpds,
            ],
            'active_dbf' => $activeFile,
            'cached_at' => date('d/m/Y H:i').' WITA',
        ];

        Cache::put($cacheKey, $result, 86400);

        return $result;
    }

    /**
     * Tampilan Halaman Laporan BNBA & Pemetaan Master SKPD / Satker SIMGAJI
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'bnba'); // 'bnba', 'master_skpd', atau 'master_satker'
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
                'satkerMappings' => new LengthAwarePaginator([], 0, 25),
                'summary' => [],
                'filterOptions' => [],
                'tab' => $tab,
                'statusFilter' => $statusFilter,
                'kategori' => $kategori,
                'skpdFilter' => $skpdFilter,
                'simgajiFilter' => $simgajiFilter,
                'search' => $search,
                'perPage' => $perPage,
                'statusSatker' => 'semua',
                'satkerSearch' => '',
                'satkerSkpd' => 'semua',
                'simpegItems' => new LengthAwarePaginator([], 0, 25),
                'totalSimpegCount' => 0,
                'totalSimpegTerdaftarSimgaji' => 0,
                'simpegSkpdOptions' => [],
                'statusPegawaiOptions' => [],
                'jenisPegawaiOptions' => [],
                'simpegSkpdFilter' => 'semua',
                'statusPegawaiFilter' => 'semua',
                'jenisPegawaiFilter' => 'semua',
                'statusSimgajiFilter' => 'semua',
                'simpegSearch' => '',
            ]);
        }

        // Tentukan dataset berdasarkan Tab
        $page = (int) $request->get('page', 1);

        // TAB 4: BNBA SIMPEG Lengkap + SIMGAJI
        $paginatedSimpeg = null;
        $simpegSkpdFilter = $request->get('simpeg_skpd', 'semua');
        $statusPegawaiFilter = $request->get('status_pegawai', 'semua');
        $jenisPegawaiFilter = $request->get('jenis_pegawai', 'semua');
        $statusSimgajiFilter = $request->get('status_simgaji', 'semua');
        $simpegSearch = trim((string) $request->get('simpeg_search', ($tab === 'simpeg_lengkap' ? $search : '')));

        $totalSimpegCount = Pegawai::count();
        $simpegSkpdOptions = [];
        $statusPegawaiOptions = [];
        $jenisPegawaiOptions = [];
        $totalSimpegTerdaftarSimgaji = 0;

        if ($tab === 'simpeg_lengkap') {
            $simgajiLookup = $this->getSimgajiPgwLookup();
            $simgajiNips = array_keys($simgajiLookup);
            $totalSimpegTerdaftarSimgaji = count($simgajiLookup);

            $simpegSkpdOptions = UnitKerja::whereNotNull('skpd')->distinct()->orderBy('skpd')->pluck('skpd')->toArray();
            $statusPegawaiOptions = Pegawai::whereNotNull('status_pegawai')->distinct()->orderBy('status_pegawai')->pluck('status_pegawai')->toArray();
            $jenisPegawaiOptions = Pegawai::whereNotNull('jenis_pegawai')->distinct()->orderBy('jenis_pegawai')->pluck('jenis_pegawai')->toArray();

            $simpegQuery = Pegawai::with(['unitKerja', 'jabatan']);

            if ($simpegSkpdFilter !== 'semua' && ! empty($simpegSkpdFilter)) {
                $simpegQuery->whereHas('unitKerja', fn ($q) => $q->where('skpd', $simpegSkpdFilter));
            }

            if ($statusPegawaiFilter !== 'semua' && ! empty($statusPegawaiFilter)) {
                $simpegQuery->where('status_pegawai', $statusPegawaiFilter);
            }

            if ($jenisPegawaiFilter !== 'semua' && ! empty($jenisPegawaiFilter)) {
                $simpegQuery->where('jenis_pegawai', $jenisPegawaiFilter);
            }

            if ($statusSimgajiFilter === 'terdaftar') {
                $simpegQuery->whereIn('nip', $simgajiNips);
            } elseif ($statusSimgajiFilter === 'belum_terdaftar') {
                $simpegQuery->whereNotIn('nip', $simgajiNips);
            }

            if ($simpegSearch !== '') {
                $s = $simpegSearch;
                $simpegQuery->where(function ($q) use ($s) {
                    $q->where('nip', 'like', "%{$s}%")
                        ->orWhere('nama', 'like', "%{$s}%")
                        ->orWhere('nik', 'like', "%{$s}%")
                        ->orWhereHas('unitKerja', function ($uq) use ($s) {
                            $uq->where('skpd', 'like', "%{$s}%")
                                ->orWhere('upt', 'like', "%{$s}%")
                                ->orWhere('satker', 'like', "%{$s}%");
                        })
                        ->orWhereHas('jabatan', function ($jq) use ($s) {
                            $jq->where('nama', 'like', "%{$s}%");
                        });
                });
            }

            $paginatedSimpeg = $simpegQuery->orderBy('unit_kerja_id')->orderBy('nama')->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

            foreach ($paginatedSimpeg as $p) {
                $nip = (string) ($p->nip ?? '');
                $sg = $simgajiLookup[$nip] ?? null;
                $p->kdskpd_simgaji = $sg ? $sg['kdskpd'] : '-';
                $p->nmskpd_simgaji = $sg ? $sg['nmskpd'] : 'Belum Terdaftar di SIMGAJI';
                $p->kdsatker_simgaji = $sg ? $sg['kdsatker'] : '-';
                $p->nmsatker_simgaji = $sg ? $sg['nmsatker'] : '-';
                $p->status_simgaji = $sg ? $sg['status_simgaji'] : 'Belum Terdaftar di SIMGAJI';
            }
        }

        // TAB 3: Master Satker
        $statusSatker = $request->get('status_satker', 'semua'); // 'semua', 'aktif', '0_pegawai'
        $satkerSearch = trim((string) $request->get('satker_search', ($tab === 'master_satker' ? $search : '')));
        $satkerSkpd = $request->get('satker_skpd', 'semua');

        $satkerColl = collect($data['satker_mappings'] ?? []);
        if ($satkerSkpd !== 'semua') {
            $satkerColl = $satkerColl->filter(fn ($s) => ($s['kdskpd'] ?? '') === $satkerSkpd || ($s['nmskpd'] ?? '') === $satkerSkpd);
        }
        if ($statusSatker === 'aktif') {
            $satkerColl = $satkerColl->filter(fn ($s) => ($s['total_pegawai'] ?? 0) > 0);
        } elseif ($statusSatker === '0_pegawai') {
            $satkerColl = $satkerColl->filter(fn ($s) => ($s['total_pegawai'] ?? 0) === 0);
        }
        if ($satkerSearch !== '') {
            $sLower = strtolower($satkerSearch);
            $satkerColl = $satkerColl->filter(function ($s) use ($sLower) {
                return str_contains(strtolower($s['kdsatker'] ?? ''), $sLower)
                    || str_contains(strtolower($s['nmsatker'] ?? ''), $sLower)
                    || str_contains(strtolower($s['nmskpd'] ?? ''), $sLower)
                    || str_contains(strtolower($s['dominan_simpeg_upt'] ?? ''), $sLower);
            });
        }

        $paginatedSatkers = new LengthAwarePaginator(
            $satkerColl->forPage($page, $perPage)->values(),
            $satkerColl->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // TAB 1: BNBA Dataset
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

        // Filter Search di BNBA
        if ($search !== '' && $tab !== 'master_satker' && $tab !== 'simpeg_lengkap') {
            $sLower = strtolower($search);
            $items = $items->filter(function ($item) use ($sLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['upt_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['satker_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nmsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

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
            'satkerMappings' => $paginatedSatkers,
            'filteredTotal' => $items->count(),
            'satkerFilteredTotal' => $satkerColl->count(),
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
            'statusSatker' => $statusSatker,
            'satkerSearch' => $satkerSearch,
            'satkerSkpd' => $satkerSkpd,
            // TAB 4: SIMPEG Lengkap
            'simpegItems' => $paginatedSimpeg,
            'totalSimpegCount' => $totalSimpegCount,
            'totalSimpegTerdaftarSimgaji' => $totalSimpegTerdaftarSimgaji,
            'simpegSkpdOptions' => $simpegSkpdOptions,
            'statusPegawaiOptions' => $statusPegawaiOptions,
            'jenisPegawaiOptions' => $jenisPegawaiOptions,
            'simpegSkpdFilter' => $simpegSkpdFilter,
            'statusPegawaiFilter' => $statusPegawaiFilter,
            'jenisPegawaiFilter' => $jenisPegawaiFilter,
            'statusSimgajiFilter' => $statusSimgajiFilter,
            'simpegSearch' => $simpegSearch,
        ]);
    }

    /**
     * Segarkan Cache Data Penyelarasan
     */
    public function refreshCache()
    {
        Cache::forget('laporan_bnba_perbaikan_simgaji_v4');
        Cache::forget('laporan_bnba_perbaikan_simgaji_v3');
        Cache::forget('laporan_bnba_perbaikan_simgaji_v2');

        $filePath = base_path('kode_skpd_satker_simgaji.xlsx');
        $mtime = file_exists($filePath) ? filemtime($filePath) : 0;
        Cache::forget('master_simgaji_excel_data_'.$mtime);

        $active = $this->getActiveDbfFile();
        if ($active && file_exists($active['path'])) {
            $mtimeDbf = filemtime($active['path']);
            Cache::forget('simgaji_pgw_lookup_'.$mtimeDbf);
        }

        $this->getBnbaData(true);

        return redirect()->back()->with('success', 'Data Master SIMGAJI, BNBA & Matriks SKPD/Satker berhasil dihitung ulang dan diselaraskan.');
    }

    /**
     * Ekspor Data BNBA & Pemetaan Master SKPD / Satker ke Microsoft Excel (.xlsx) Multi-Sheet
     */
    public function exportExcel(Request $request)
    {
        $scope = $request->get('scope', 'perbaikan'); // 'perbaikan' atau 'full'/'semua' atau 'simpeg_lengkap'
        if ($scope === 'simpeg_lengkap') {
            return $this->exportSimpegLengkap($request);
        }
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
                    || str_contains(strtolower($item['satker_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nmsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

        $spreadsheet = new Spreadsheet;

        // ==========================================
        // SHEET 1: PEMETAAN MASTER SKPD (62 SKPD)
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('1. Master SKPD SIMGAJI');

        $sheet1->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet1->setCellValue('A2', 'MATRIKS PEMETAAN MASTER KODE & NAMA SKPD SIMGAJI TASPEN VS SIMPEG (ACUAN RESMI)');
        $sheet1->setCellValue('A3', 'Acuan Master Data Resmi: SIMPEG & File Master SIMGAJI | '.count($data['skpd_mappings'] ?? []).' Kode SKPD SIMGAJI | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet1->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet1->getStyle('A1')->getFont()->setSize(13);
        $sheet1->getStyle('A2')->getFont()->setSize(11);
        $sheet1->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $headers1 = [
            'No',
            'Kode SKPD SIMGAJI',
            'Nama SKPD di SIMGAJI (Eksisting)',
            'Jumlah Satker SIMGAJI',
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
                $m['total_satker'],
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
            $sheet1->getStyle("D{$rowNum1}:D{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("F{$rowNum1}:F{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("G{$rowNum1}:J{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("K{$rowNum1}:K{$endRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', $lastCol1) as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 2: MASTER SATKER SIMGAJI (551 SATKER)
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('2. Master Satker SIMGAJI');

        $sheet2->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet2->setCellValue('A2', 'MATRIKS MASTER DATA SATUAN KERJA (SATKER) SIMGAJI TASPEN (551 SATKER)');
        $sheet2->setCellValue('A3', 'Sumber Master: File Master SIMGAJI (kode_skpd_satker_simgaji.xlsx) | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet2->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet2->getStyle('A1')->getFont()->setSize(13);
        $sheet2->getStyle('A2')->getFont()->setSize(11);
        $sheet2->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $headers2 = [
            'No',
            'Kode SKPD',
            'Nama SKPD SIMGAJI',
            'Kode Satker SIMGAJI',
            'Nama Satker Resmi SIMGAJI',
            'Total Pegawai Aktif (DBF)',
            'Dominan UPTD SIMPEG',
            'Status Keaktifan',
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
                'startColor' => ['rgb' => '047857'], // Emerald Dark
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet2->getRowDimension($rowNum2)->setRowHeight(28);

        $no2 = 1;
        $rowNum2++;
        $tableData2 = [];
        foreach ($data['satker_mappings'] ?? [] as $sat) {
            $tableData2[] = [
                $no2++,
                $sat['kdskpd'],
                $sat['nmskpd'],
                $sat['kdsatker'],
                $sat['nmsatker'],
                $sat['total_pegawai'],
                $sat['dominan_simpeg_upt'],
                $sat['status'],
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
            $sheet2->getStyle("D{$rowNum2}:D{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("F{$rowNum2}:F{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle("H{$rowNum2}:H{$endRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', $lastCol2) as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 3: DATA BNBA PEGAWAI
        // ==========================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3Title = ($scope === 'full' || $scope === 'semua' || $statusFilter === 'semua') ? '3. Full BNBA Pegawai' : '3. BNBA Perbaikan Pegawai';
        $sheet3->setTitle($sheet3Title);

        $sheet3->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet3->setCellValue('A2', 'LAPORAN DATA BNBA (BY NAME BY ADDRESS) PEMETAAN & USULAN PERBAIKAN SKPD SIMGAJI');
        $sheet3->setCellValue('A3', 'Acuan Resmi: SIMPEG / KONBELPEG | Total Data: '.number_format($items->count(), 0, ',', '.').' Pegawai | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet3->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet3->getStyle('A1')->getFont()->setSize(13);
        $sheet3->getStyle('A2')->getFont()->setSize(11);
        $sheet3->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $headers3 = [
            'No',
            'NIP',
            'Nama Pegawai',
            'Gol/Ruang',
            'Status Pegawai',
            'Jabatan Resmi (SIMPEG)',
            'Kode SKPD SIMGAJI',
            'Nama SKPD SIMGAJI (Eksisting)',
            'Kode Satker SIMGAJI',
            'Nama Satker SIMGAJI (Eksisting)',
            'Inputer SIMGAJI',
            'SKPD Acuan Resmi (SIMPEG)',
            'Kode Rekomendasi SKPD',
            'Kode Rekomendasi Satker',
            'Satker Rekomendasi (SIMGAJI)',
            'UPTD Resmi (SIMPEG)',
            'Satker / Sub Unit Resmi (SIMPEG)',
            'Status / Jenis Selisih',
            'Rekomendasi Tindakan Perbaikan SIMGAJI',
        ];

        $rowNum3 = 5;
        $colLetter = 'A';
        foreach ($headers3 as $h) {
            $sheet3->setCellValue($colLetter.$rowNum3, $h);
            $colLetter++;
        }
        $lastCol3 = chr(ord('A') + count($headers3) - 1);

        $sheet3->getStyle("A{$rowNum3}:{$lastCol3}{$rowNum3}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Brand Primary Dark Blue
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet3->getRowDimension($rowNum3)->setRowHeight(28);

        $no3 = 1;
        $rowNum3++;
        $tableData3 = [];
        foreach ($items as $row) {
            $tableData3[] = [
                $no3++,
                $row['nip'],
                $row['nama'],
                $row['golru'],
                $row['status_pegawai'],
                $row['jabatan'],
                $row['kdskpd_simgaji'],
                $row['skpd_simgaji'],
                $row['kdsatker_simgaji'],
                $row['nmsatker_simgaji'] ?? '-',
                $row['inputer_simgaji'],
                $row['skpd_simpeg'],
                $row['kdskpd_rekomendasi'],
                $row['kdsatker_rekomendasi'] ?? '-',
                $row['satker_rekomendasi'] ?? '-',
                $row['upt_simpeg'] ?? '-',
                $row['satker_simpeg'] ?? '-',
                $row['jenis_selisih'],
                $row['rekomendasi'],
            ];
        }

        $sheet3->fromArray($tableData3, null, "A{$rowNum3}");
        $endRow3 = $rowNum3 + count($tableData3) - 1;

        if ($endRow3 >= $rowNum3) {
            $sheet3->getStyle("A{$rowNum3}:{$lastCol3}{$endRow3}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet3->getStyle("A{$rowNum3}:A{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("B{$rowNum3}:B{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("D{$rowNum3}:E{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("G{$rowNum3}:G{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("I{$rowNum3}:I{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("M{$rowNum3}:N{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("Q{$rowNum3}:Q{$endRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', $lastCol3) as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // Kembali ke sheet 1 sebagai default tampilan
        $spreadsheet->setActiveSheetIndex(0);

        $suffix = ($scope === 'full' || $scope === 'semua' || $statusFilter === 'semua') ? 'FULL_PEMETAAN' : 'PERBAIKAN';
        $filename = "Pemetaan_SKPD_Satker_SIMGAJI_vs_SIMPEG_{$suffix}_".date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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

        if ($search !== '' && $tab !== 'master_satker') {
            $sLower = strtolower($search);
            $items = $items->filter(function ($item) use ($sLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['upt_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['satker_simpeg'] ?? ''), $sLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdskpd_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['kdsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['nmsatker_simgaji'] ?? ''), $sLower)
                    || str_contains(strtolower($item['jabatan'] ?? ''), $sLower)
                    || str_contains(strtolower($item['rekomendasi'] ?? ''), $sLower);
            });
        }

        ini_set('memory_limit', '1024M');
        $isLimited = false;
        if ($items->count() > 250) {
            $items = $items->take(250);
            $isLimited = true;
        }

        $satkerMappings = collect($data['satker_mappings'] ?? []);
        if ($skpdFilter !== 'semua') {
            $satkerMappings = $satkerMappings->filter(fn ($s) => ($s['kdskpd'] ?? '') === $skpdFilter || ($s['nmskpd'] ?? '') === $skpdFilter);
        }
        if ($search !== '' && $tab === 'master_satker') {
            $sLower = strtolower($search);
            $satkerMappings = $satkerMappings->filter(function ($s) use ($sLower) {
                return str_contains(strtolower($s['kdsatker'] ?? ''), $sLower)
                    || str_contains(strtolower($s['nmsatker'] ?? ''), $sLower)
                    || str_contains(strtolower($s['nmskpd'] ?? ''), $sLower)
                    || str_contains(strtolower($s['dominan_simpeg_upt'] ?? ''), $sLower);
            });
        }
        if ($satkerMappings->count() > 150) {
            $satkerMappings = $satkerMappings->take(150);
            $isLimited = true;
        }

        $pdf = Pdf::loadView('laporan.perbaikan_simgaji_skpd.pdf', [
            'items' => $items,
            'skpdMappings' => $data['skpd_mappings'] ?? [],
            'satkerMappings' => $satkerMappings,
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

        return $pdf->stream('Laporan_Perbaikan_SIMGAJI_'.strtoupper($tab).'_'.date('Ymd_His').'.pdf');
    }

    /**
     * Ekspor Data BNBA Pegawai SIMPEG Lengkap Disandingkan Data SIMGAJI ke Excel (.xlsx)
     */
    public function exportSimpegLengkap(Request $request)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $skpdFilter = $request->get('simpeg_skpd', $request->get('skpd', 'semua'));
        $statusPegawai = $request->get('status_pegawai', 'semua');
        $jenisPegawai = $request->get('jenis_pegawai', 'semua');
        $statusSimgaji = $request->get('status_simgaji', 'semua');
        $search = trim((string) $request->get('simpeg_search', $request->get('search', '')));

        $simgajiLookup = $this->getSimgajiPgwLookup();
        $simgajiNips = array_keys($simgajiLookup);

        $query = Pegawai::with(['unitKerja', 'jabatan']);

        if ($skpdFilter !== 'semua' && ! empty($skpdFilter)) {
            $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }

        if ($statusPegawai !== 'semua' && ! empty($statusPegawai)) {
            $query->where('status_pegawai', $statusPegawai);
        }

        if ($jenisPegawai !== 'semua' && ! empty($jenisPegawai)) {
            $query->where('jenis_pegawai', $jenisPegawai);
        }

        if ($statusSimgaji === 'terdaftar') {
            $query->whereIn('nip', $simgajiNips);
        } elseif ($statusSimgaji === 'belum_terdaftar') {
            $query->whereNotIn('nip', $simgajiNips);
        }

        if ($search !== '') {
            $s = $search;
            $query->where(function ($q) use ($s) {
                $q->where('nip', 'like', "%{$s}%")
                    ->orWhere('nama', 'like', "%{$s}%")
                    ->orWhere('nik', 'like', "%{$s}%")
                    ->orWhereHas('unitKerja', function ($uq) use ($s) {
                        $uq->where('skpd', 'like', "%{$s}%")
                            ->orWhere('upt', 'like', "%{$s}%")
                            ->orWhere('satker', 'like', "%{$s}%");
                    })
                    ->orWhereHas('jabatan', function ($jq) use ($s) {
                        $jq->where('nama', 'like', "%{$s}%");
                    });
            });
        }

        $pegawais = $query->orderBy('unit_kerja_id')->orderBy('nama')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SIMPEG Lengkap + SIMGAJI');

        // Title Block
        $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet->setCellValue('A2', 'LAPORAN DATA BNBA PEGAWAI SIMPEG LENGKAP & INTEGRASI DATA SIMGAJI TASPEN');
        $filterDesc = 'Total Data: '.number_format($pegawais->count(), 0, ',', '.').' Pegawai | Sumber: SIMPEG & SIMGAJI | Waktu Unduh: '.date('d/m/Y H:i').' WITA';
        if ($skpdFilter !== 'semua') {
            $filterDesc .= ' | SKPD: '.$skpdFilter;
        }
        if ($statusPegawai !== 'semua') {
            $filterDesc .= ' | Status: '.$statusPegawai;
        }
        if ($statusSimgaji !== 'semua') {
            $filterDesc .= ' | Status SIMGAJI: '.($statusSimgaji === 'terdaftar' ? 'Terdaftar di SIMGAJI' : 'Belum di SIMGAJI');
        }
        $sheet->setCellValue('A3', $filterDesc);

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        // Group Headers (Row 5)
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', 'No');

        $sheet->mergeCells('B5:H5');
        $sheet->setCellValue('B5', 'IDENTITAS PEGAWAI (SIMPEG)');

        $sheet->mergeCells('I5:N5');
        $sheet->setCellValue('I5', 'STATUS KEPEGAWAIAN & PANGKAT (SIMPEG)');

        $sheet->mergeCells('O5:U5');
        $sheet->setCellValue('O5', 'JABATAN & PENDIDIKAN (SIMPEG)');

        $sheet->mergeCells('V5:X5');
        $sheet->setCellValue('V5', 'ACUAN RESMI UNIT KERJA (SIMPEG)');

        $sheet->mergeCells('Y5:AB5');
        $sheet->setCellValue('Y5', 'DATA FINANSIAL & ADMINISTRASI (SIMPEG)');

        $sheet->mergeCells('AC5:AF5');
        $sheet->setCellValue('AC5', 'DATA SIMGAJI TASPEN (EKSISTING)');

        $sheet->mergeCells('AG5:AG6');
        $sheet->setCellValue('AG5', 'Status di SIMGAJI');

        // Sub Headers (Row 6)
        $subHeaders = [
            'B' => 'NIP',
            'C' => 'Nama Pegawai',
            'D' => 'NIK',
            'E' => 'Tempat Lahir',
            'F' => 'Tanggal Lahir',
            'G' => 'Jenis Kelamin',
            'H' => 'Agama',
            'I' => 'Status Pegawai',
            'J' => 'Jenis Pegawai',
            'K' => 'Gol/Ruang',
            'L' => 'TMT Golru',
            'M' => 'Masa Kerja (Thn)',
            'N' => 'Masa Kerja (Bln)',
            'O' => 'Jabatan Resmi (SIMPEG)',
            'P' => 'Eselon',
            'Q' => 'Jenis Jabatan',
            'R' => 'TMT Jabatan',
            'S' => 'Tingkat Ijazah',
            'T' => 'Nama Pendidikan / Jurusan',
            'U' => 'Tahun Lulus',
            'V' => 'SKPD Resmi (SIMPEG)',
            'W' => 'UPTD Resmi (SIMPEG)',
            'X' => 'Satker / Sub Unit Resmi (SIMPEG)',
            'Y' => 'Nomor Rekening',
            'Z' => 'Nama Bank',
            'AA' => 'NPWP',
            'AB' => 'Nomor Karpeg',
            // 4 Kolom Khusus Permintaan User
            'AC' => 'Kd SKPD',
            'AD' => 'Nama SKPD SIMGAJI',
            'AE' => 'Kode Satker',
            'AF' => 'Nama Satker Resmi SIMGAJI',
        ];

        foreach ($subHeaders as $col => $label) {
            $sheet->setCellValue($col.'6', $label);
        }

        // Apply Styles to Row 5 & 6
        $sheet->getStyle('A5:AG6')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:AG6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A5:AG6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        $sheet->getStyle('A5:A6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('B5:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A'); // Blue
        $sheet->getStyle('I5:N6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857'); // Emerald
        $sheet->getStyle('O5:U6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4338CA'); // Indigo
        $sheet->getStyle('V5:X6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E'); // Teal
        $sheet->getStyle('Y5:AB6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B45309'); // Amber
        $sheet->getStyle('AC5:AF6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A'); // Slate Dark (SIMGAJI)
        $sheet->getStyle('AG5:AG6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('374151'); // Charcoal

        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Populate Data
        $dataRows = [];
        $no = 1;
        foreach ($pegawais as $p) {
            $nip = (string) ($p->nip ?? '');
            $sg = $simgajiLookup[$nip] ?? null;

            $kdSkpdSimgaji = $sg ? (string) $sg['kdskpd'] : '-';
            $nmSkpdSimgaji = $sg ? (string) $sg['nmskpd'] : 'Belum Terdaftar di SIMGAJI';
            $kdSatkerSimgaji = $sg ? (string) $sg['kdsatker'] : '-';
            $nmSatkerSimgaji = $sg ? (string) $sg['nmsatker'] : '-';
            $statusSimgaji = $sg ? (string) $sg['status_simgaji'] : 'Belum di SIMGAJI';

            $dataRows[] = [
                $no++,
                $nip,
                $p->nama ?? '-',
                $p->nik ? (string) $p->nik : '-',
                $p->tempat_lahir ?? '-',
                $p->tgl_lahir ? (string) $p->tgl_lahir : '-',
                $p->jk ?? '-',
                $p->agama ?? '-',
                $p->status_pegawai ?? '-',
                $p->jenis_pegawai ?? '-',
                $p->golru ?? '-',
                $p->tmt_golru ?? '-',
                $p->masa_kerja_tahun !== null ? (int) $p->masa_kerja_tahun : '-',
                $p->masa_kerja_bulan !== null ? (int) $p->masa_kerja_bulan : '-',
                $p->jabatan?->nama ?? '-',
                $p->jabatan?->eselon ?? '-',
                $p->jabatan?->jenis ?? '-',
                $p->tmt_jabatan ?? '-',
                $p->tk_ijazah ?? '-',
                $p->nm_pendidikan ?? '-',
                $p->th_lulus !== null ? (int) $p->th_lulus : '-',
                $p->unitKerja?->skpd ?? '-',
                $p->unitKerja?->upt ?? '-',
                $p->unitKerja?->satker ?? '-',
                $p->no_rekening ? (string) $p->no_rekening : '-',
                $p->nama_bank ?? '-',
                $p->npwp ? (string) $p->npwp : '-',
                $p->no_karpeg ? (string) $p->no_karpeg : '-',
                $kdSkpdSimgaji,
                $nmSkpdSimgaji,
                $kdSatkerSimgaji,
                $nmSatkerSimgaji,
                $statusSimgaji,
            ];
        }

        $startRow = 7;
        $sheet->fromArray($dataRows, null, "A{$startRow}");
        $endRow = $startRow + count($dataRows) - 1;

        if ($endRow >= $startRow) {
            $sheet->getStyle("A{$startRow}:AG{$endRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Format text
            $sheet->getStyle("B{$startRow}:B{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("D{$startRow}:D{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("Y{$startRow}:Y{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("AA{$startRow}:AB{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("AC{$startRow}:AC{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("AE{$startRow}:AE{$endRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            // Alignments
            $sheet->getStyle("A{$startRow}:A{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startRow}:B{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$startRow}:D{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$startRow}:H{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$startRow}:N{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("P{$startRow}:R{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("S{$startRow}:S{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("U{$startRow}:U{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("AC{$startRow}:AC{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("AE{$startRow}:AE{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("AG{$startRow}:AG{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG'];
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_BNBA_SIMPEG_Lengkap_vs_SIMGAJI_'.date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
