<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
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
        $cacheKey = 'laporan_bnba_perbaikan_simgaji_v2';
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

        // 2. Baca file DBF SIMGAJI (Tahap 1: Pemetaan Satker Dominan)
        $table = new TableReader($activeFile['path']);
        $satkerMap = [];
        $rawRecords = [];
        $totalAktifSimgaji = 0;

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

            $totalAktifSimgaji++;

            // Data SIMPEG
            $simpegPgw = $dbPegawais[$nip] ?? null;
            $simpegSkpd = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->skpd) : '-';
            $simpegUpt = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->upt) : '-';
            $simpegSatker = $simpegPgw && $simpegPgw->unitKerja ? trim((string) $simpegPgw->unitKerja->satker) : '-';

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

        // 3. Bangun Data BNBA Perbaikan SIMGAJI
        $bnbaList = [];

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

            // Hanya proses pegawai yang terdaftar di SIMPEG sebagai acuan dasar kebenaran
            if (! $simpegPgw) {
                continue;
            }

            $golru = $simpegPgw->golru ?: '-';
            $jabatan = $simpegPgw->jabatan ? trim($simpegPgw->jabatan->nama) : '-';
            $statusPegawai = $simpegPgw->status_pegawai ?: '-';

            // Kasus 2: Beda SKPD Induk (Mutasi Antar-SKPD)
            $isBedaSkpdInduk = strcasecmp($cleanSimgajiSkpd, $skpdSimpeg) !== 0;
            if ($isBedaSkpdInduk) {
                $kdRekomendasi = $simpegToSimgajiCode[$skpdSimpeg] ?? '-';

                $bnbaList[] = [
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
                    'priority' => 1,
                    'rekomendasi' => "Pindahkan SKPD di SIMGAJI ke: {$skpdSimpeg}".($kdRekomendasi !== '-' ? " (Kode: {$kdRekomendasi})" : '').($uptSimpeg !== '-' ? " & Satker: {$uptSimpeg}" : ''),
                    'keterangan' => "SIMGAJI: {$simgajiSkpdName} (Kode {$kdskpd}) | SIMPEG Resmi: {$skpdSimpeg}",
                ];

                continue;
            }

            // Kasus 3: Beda Cabang Dinas Pendidikan (Kabupaten / Kota)
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
                    $bnbaList[] = [
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
                        'priority' => 2,
                        'rekomendasi' => "Pindahkan Cabang Disdik SIMGAJI ke: {$targetDisdik['nama']} (Kode: {$targetDisdik['code']}) & Satker: {$uptSimpeg}",
                        'keterangan' => "SIMGAJI: Cabang {$kdskpd} | Unit Resmi SIMPEG: {$uptSimpeg}",
                    ];

                    continue;
                }
            }

            // Kasus 4: Beda UPTD / Sekolah / Satker di SKPD yang Sama
            $sKey = $kdskpd.'|'.$kdsatker;
            if (isset($satkerDominantUpt[$sKey]) && $satkerDominantUpt[$sKey]['total_upt_pgw'] >= 3) {
                $domUpt = $satkerDominantUpt[$sKey]['dominant_upt'];
                if ($uptSimpeg !== '-' && strcasecmp($uptSimpeg, $domUpt) !== 0) {
                    $kdRekomendasi = $kdskpd;

                    $bnbaList[] = [
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
                        'priority' => 3,
                        'rekomendasi' => "Sesuaikan penempatan Satker SIMGAJI ke: {$uptSimpeg}",
                        'keterangan' => "SIMPEG: {$uptSimpeg} (Satker SIMGAJI saat ini dominan: {$domUpt})",
                    ];
                }
            }
        }

        // Urutkan data berdasarkan prioritas (Beda SKPD Induk, Beda Cabang Disdik, Beda UPTD), lalu SKPD, lalu Nama
        usort($bnbaList, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            $cmpSkpd = strcmp($a['skpd_simpeg'], $b['skpd_simpeg']);
            if ($cmpSkpd !== 0) {
                return $cmpSkpd;
            }

            return strcmp($a['nama'], $b['nama']);
        });

        // Rekapitulasi Statistik
        $coll = collect($bnbaList);
        $totalBedaSkpd = $coll->where('jenis_selisih', 'Beda SKPD Induk')->count();
        $totalBedaCabangDisdik = $coll->where('jenis_selisih', 'Beda Cabang Disdik')->count();
        $totalBedaUptd = $coll->where('jenis_selisih', 'Beda UPTD / Satker')->count();
        $totalTidakDiSimpeg = $coll->where('jenis_selisih', 'Tidak Terdaftar di SIMPEG')->count();
        $allSimpegSkpds = $coll->pluck('skpd_simpeg')->unique()->sort()->values()->toArray();
        $allSimgajiSkpds = $coll->pluck('skpd_simgaji')->unique()->sort()->values()->toArray();

        $result = [
            'bnba_items' => $bnbaList,
            'summary' => [
                'total_perlu_perbaikan' => count($bnbaList),
                'total_beda_skpd' => $totalBedaSkpd,
                'total_beda_cabang_disdik' => $totalBedaCabangDisdik,
                'total_beda_uptd' => $totalBedaUptd,
                'total_tidak_di_simpeg' => $totalTidakDiSimpeg,
                'total_skpd_terdampak' => count($allSimpegSkpds),
                'total_aktif_simgaji' => $totalAktifSimgaji,
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
     * Tampilan Halaman Laporan BNBA Perbaikan SIMGAJI
     */
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));

        $data = $this->getBnbaData();

        if (isset($data['error'])) {
            return view('laporan.perbaikan_simgaji_skpd.index', [
                'error' => $data['error'],
                'items' => new LengthAwarePaginator([], 0, 25),
                'summary' => [],
                'filterOptions' => [],
                'kategori' => $kategori,
                'skpdFilter' => $skpdFilter,
                'simgajiFilter' => $simgajiFilter,
                'search' => $search,
            ]);
        }

        $items = collect($data['bnba_items'] ?? []);

        // Filter Kategori
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
        $perPage = 25;
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
            'filteredTotal' => $items->count(),
            'summary' => $data['summary'] ?? [],
            'filterOptions' => $data['filter_options'] ?? [],
            'activeDbf' => $data['active_dbf'] ?? null,
            'cachedAt' => $data['cached_at'] ?? null,
            'kategori' => $kategori,
            'skpdFilter' => $skpdFilter,
            'simgajiFilter' => $simgajiFilter,
            'search' => $search,
        ]);
    }

    /**
     * Segarkan Cache Data Penyelarasan
     */
    public function refreshCache()
    {
        Cache::forget('laporan_bnba_perbaikan_simgaji_v2');
        $this->getBnbaData(true);

        return redirect()->back()->with('success', 'Data BNBA Perbaikan SIMGAJI berhasil dihitung ulang dan diperbarui.');
    }

    /**
     * Ekspor Data BNBA ke Microsoft Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));

        $data = $this->getBnbaData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data['bnba_items'] ?? []);

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
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BNBA Perbaikan SIMGAJI');

        // Header Dokumen Resmi
        $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet->setCellValue('A2', 'LAPORAN DATA BNBA (BY NAME BY ADDRESS) USULAN PERBAIKAN SKPD & SATKER SIMGAJI TASPEN');
        $sheet->setCellValue('A3', 'Acuan Master Data Resmi: SIMPEG / KONBELPEG | File DBF: '.($data['active_dbf']['filename'] ?? '-').' | Waktu Unduh: '.date('d/m/Y H:i').' WITA');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        // Header Tabel Kolom
        $rowNum = 5;
        $headers = [
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
            'Jenis Selisih',
            'Rekomendasi Tindakan Perbaikan SIMGAJI',
        ];

        $colLetter = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($colLetter.$rowNum, $h);
            $colLetter++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);

        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Brand Primary Dark Blue
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getRowDimension($rowNum)->setRowHeight(28);

        // Isi Data
        $no = 1;
        $rowNum++;
        foreach ($items as $row) {
            $sheet->setCellValueExplicit("A{$rowNum}", $no++, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("B{$rowNum}", $row['nip'], DataType::TYPE_STRING);
            $sheet->setCellValue("C{$rowNum}", $row['nama']);
            $sheet->setCellValue("D{$rowNum}", $row['golru']);
            $sheet->setCellValue("E{$rowNum}", $row['status_pegawai']);
            $sheet->setCellValue("F{$rowNum}", $row['jabatan']);
            $sheet->setCellValueExplicit("G{$rowNum}", $row['kdskpd_simgaji'], DataType::TYPE_STRING);
            $sheet->setCellValue("H{$rowNum}", $row['skpd_simgaji']);
            $sheet->setCellValueExplicit("I{$rowNum}", $row['kdsatker_simgaji'], DataType::TYPE_STRING);
            $sheet->setCellValue("J{$rowNum}", $row['inputer_simgaji']);
            $sheet->setCellValue("K{$rowNum}", $row['skpd_simpeg']);
            $sheet->setCellValueExplicit("L{$rowNum}", $row['kdskpd_rekomendasi'], DataType::TYPE_STRING);
            $sheet->setCellValue("M{$rowNum}", $row['upt_simpeg']);
            $sheet->setCellValue("N{$rowNum}", $row['jenis_selisih']);
            $sheet->setCellValue("O{$rowNum}", $row['rekomendasi']);

            // Styling Baris
            $fillColor = ($no % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Alignment khusus kolom
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;
        }

        // Auto width untuk setiap kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'BNBA_Perbaikan_SKPD_SIMGAJI_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Ekspor Data BNBA ke Dokumen PDF Resmi (A4 Landscape)
     */
    public function exportPdf(Request $request)
    {
        $kategori = $request->get('kategori', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');
        $simgajiFilter = $request->get('skpd_simgaji', 'semua');
        $search = trim((string) $request->get('search', ''));

        $data = $this->getBnbaData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data['bnba_items'] ?? []);

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
            'summary' => $data['summary'] ?? [],
            'activeFile' => $data['active_dbf'] ?? [],
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
