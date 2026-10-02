<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
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

class PenyelarasanUnitKerjaController extends Controller
{
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

    private function getDbfManifest(): array
    {
        $manifestPath = storage_path('app/simgaji/manifest.json');
        $files = [];

        if (file_exists($manifestPath)) {
            $files = json_decode(file_get_contents($manifestPath), true) ?: [];
        }

        $hasMst = false;
        foreach ($files as $f) {
            if (($f['type'] ?? '') === 'mst_pgw') {
                $hasMst = true;
            }
        }

        if (! $hasMst) {
            $defaultFile = base_path('MST_PGW_2026-9-011600.DBF');
            if (file_exists($defaultFile)) {
                $files[] = [
                    'id' => 'default_mst_pgw',
                    'type' => 'mst_pgw',
                    'filename' => basename($defaultFile),
                    'stored_name' => basename($defaultFile),
                    'path' => $defaultFile,
                    'size' => round(filesize($defaultFile) / (1024 * 1024), 2).' MB',
                    'records' => 20097,
                    'keterangan' => 'Database Master Pegawai Awal (Bawaan)',
                    'uploaded_at' => date('d M Y H:i', filemtime($defaultFile)),
                    'is_active' => true,
                ];
            }
        }

        return $files;
    }

    private function getActiveDbfFile(string $type = 'mst_pgw'): ?array
    {
        $files = $this->getDbfManifest();
        foreach ($files as $f) {
            $fType = $f['type'] ?? 'mst_pgw';
            if ($fType === $type && ! empty($f['is_active']) && file_exists($f['path'])) {
                return $f;
            }
        }
        foreach ($files as $f) {
            $fType = $f['type'] ?? 'mst_pgw';
            if ($fType === $type && file_exists($f['path'])) {
                return $f;
            }
        }

        return null;
    }

    /**
     * Mengambil & Mengolah Data Penyelarasan SKPD & UPTD (SIMGAJI vs SIMPEG)
     */
    public function getPenyelarasanData(bool $forceRefresh = false): array
    {
        $cacheKey = 'penyelarasan_unit_kerja_data_v2';
        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $activeFile = $this->getActiveDbfFile('mst_pgw');
        if (! $activeFile || ! file_exists($activeFile['path'])) {
            return [
                'error' => 'File Master SIMGAJI (MST_PGW) tidak ditemukan. Silakan unggah database melalui menu Upload Master SIMGAJI.',
            ];
        }

        // 1. Ambil data master SIMPEG dari Database
        $dbPegawais = Pegawai::with(['unitKerja:id,skpd,upt,satker'])->get()->keyBy('nip');

        // Hitung total pegawai SIMPEG per SKPD
        $simpegSkpdCounts = [];
        foreach ($dbPegawais as $pgw) {
            $skpdName = $pgw->unitKerja ? trim((string) $pgw->unitKerja->skpd) : null;
            if ($skpdName) {
                $simpegSkpdCounts[$skpdName] = ($simpegSkpdCounts[$skpdName] ?? 0) + 1;
            }
        }

        // 2. Baca file DBF SIMGAJI (Tahap 1: Pembacaan & Pemetaan Satker/SKPD)
        $table = new TableReader($activeFile['path']);

        $simgajiSkpdMap = [];
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

            // Simpan record aktif untuk diproses deteksi selisih UPT
            $rawRecords[] = [
                'nip' => $nip,
                'nama' => $nama,
                'kdskpd' => $kdskpd,
                'kdsatker' => $kdsatker,
                'inputer' => $inputer,
                'simpeg_pgw' => $simpegPgw !== null,
                'skpd_simpeg' => $simpegSkpd,
                'upt_simpeg' => $simpegUpt,
                'satker_simpeg' => $simpegSatker,
            ];

            // --- A. Akumulasi Level SKPD SIMGAJI ---
            if (! isset($simgajiSkpdMap[$kdskpd])) {
                $simgajiSkpdMap[$kdskpd] = [
                    'kdskpd' => $kdskpd,
                    'nama_simgaji' => $this->skpdCodeMap[$kdskpd] ?? 'TIDAK TERDAFTAR (KODE '.$kdskpd.')',
                    'jml_simgaji' => 0,
                    'simpeg_skpds' => [],
                    'satkers' => [],
                    'inputers' => [],
                    'tidak_di_simpeg' => 0,
                ];
            }
            $simgajiSkpdMap[$kdskpd]['jml_simgaji']++;
            if ($kdsatker) {
                $simgajiSkpdMap[$kdskpd]['satkers'][$kdsatker] = true;
            }
            if ($inputer) {
                $simgajiSkpdMap[$kdskpd]['inputers'][$inputer] = true;
            }

            if ($simpegSkpd !== '-') {
                $simgajiSkpdMap[$kdskpd]['simpeg_skpds'][$simpegSkpd] = ($simgajiSkpdMap[$kdskpd]['simpeg_skpds'][$simpegSkpd] ?? 0) + 1;
            } else {
                $simgajiSkpdMap[$kdskpd]['tidak_di_simpeg']++;
            }

            // --- B. Akumulasi Level Satker SIMGAJI ---
            $satkerKey = $kdskpd.'|'.$kdsatker;
            if (! isset($satkerMap[$satkerKey])) {
                $satkerMap[$satkerKey] = [
                    'kdskpd' => $kdskpd,
                    'kdsatker' => $kdsatker ?: '-',
                    'nama_skpd_simgaji' => $this->skpdCodeMap[$kdskpd] ?? 'KODE '.$kdskpd,
                    'inputers' => [],
                    'jml_simgaji' => 0,
                    'simpeg_upts' => [],
                    'simpeg_satkers' => [],
                    'simpeg_skpds' => [],
                    'tidak_di_simpeg' => 0,
                ];
            }
            $satkerMap[$satkerKey]['jml_simgaji']++;
            if ($inputer) {
                $satkerMap[$satkerKey]['inputers'][$inputer] = true;
            }

            if ($simpegPgw) {
                if ($simpegSkpd !== '-') {
                    $satkerMap[$satkerKey]['simpeg_skpds'][$simpegSkpd] = ($satkerMap[$satkerKey]['simpeg_skpds'][$simpegSkpd] ?? 0) + 1;
                }
                if ($simpegUpt !== '-') {
                    $satkerMap[$satkerKey]['simpeg_upts'][$simpegUpt] = ($satkerMap[$satkerKey]['simpeg_upts'][$simpegUpt] ?? 0) + 1;
                }
                if ($simpegSatker !== '-') {
                    $satkerMap[$satkerKey]['simpeg_satkers'][$simpegSatker] = ($satkerMap[$satkerKey]['simpeg_satkers'][$simpegSatker] ?? 0) + 1;
                }
            } else {
                $satkerMap[$satkerKey]['tidak_di_simpeg']++;
            }
        }

        // 3. Tentukan UPT Dominan untuk Tiap Satker SIMGAJI
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

        // 4. Deteksi Selisih Pegawai (Beda SKPD Induk & Beda UPTD Sekolah/Pusat)
        $bedaPegawai = [];
        foreach ($rawRecords as $r) {
            if (! $r['simpeg_pgw']) {
                continue;
            }

            $mappedSimgajiSkpd = $this->skpdCodeMap[$r['kdskpd']] ?? null;
            // Bersihkan nama cabang wilayah misal "(KOTA BANJARMASIN)"
            $cleanMappedSkpd = $mappedSimgajiSkpd ? trim((string) preg_replace('/\s*\(.*?\)/', '', $mappedSimgajiSkpd)) : null;

            // Cek 1: Beda SKPD Induk
            $isBedaSkpd = $cleanMappedSkpd && strcasecmp(trim($cleanMappedSkpd), trim($r['skpd_simpeg'])) !== 0;
            if ($isBedaSkpd) {
                $bedaPegawai[] = [
                    'nip' => $r['nip'],
                    'nama' => $r['nama'],
                    'skpd_simpeg' => $r['skpd_simpeg'],
                    'upt_simpeg' => $r['upt_simpeg'],
                    'satker_simpeg' => $r['satker_simpeg'],
                    'kdskpd_simgaji' => $r['kdskpd'],
                    'skpd_simgaji' => $mappedSimgajiSkpd ?: 'KODE '.$r['kdskpd'],
                    'kdsatker_simgaji' => $r['kdsatker'] ?: '-',
                    'inputer_simgaji' => $r['inputer'] ?: '-',
                    'jenis_selisih' => 'Beda SKPD Induk',
                    'keterangan' => "SIMPEG: {$r['skpd_simpeg']} | SIMGAJI: {$mappedSimgajiSkpd}",
                ];

                continue;
            }

            // Cek 2: Beda UPTD di Satker SIMGAJI
            $sKey = $r['kdskpd'].'|'.$r['kdsatker'];
            if (isset($satkerDominantUpt[$sKey]) && $satkerDominantUpt[$sKey]['total_upt_pgw'] >= 3) {
                $domUpt = $satkerDominantUpt[$sKey]['dominant_upt'];
                if ($r['upt_simpeg'] !== '-' && strcasecmp($r['upt_simpeg'], $domUpt) !== 0) {
                    $bedaPegawai[] = [
                        'nip' => $r['nip'],
                        'nama' => $r['nama'],
                        'skpd_simpeg' => $r['skpd_simpeg'],
                        'upt_simpeg' => $r['upt_simpeg'],
                        'satker_simpeg' => $r['satker_simpeg'],
                        'kdskpd_simgaji' => $r['kdskpd'],
                        'skpd_simgaji' => $mappedSimgajiSkpd ?: 'KODE '.$r['kdskpd'],
                        'kdsatker_simgaji' => $r['kdsatker'] ?: '-',
                        'inputer_simgaji' => $r['inputer'] ?: '-',
                        'jenis_selisih' => 'Beda UPTD / Sekolah',
                        'keterangan' => "SIMPEG: {$r['upt_simpeg']} (Dominan Satker SIMGAJI: {$domUpt})",
                    ];
                }
            }
        }

        // 5. Rekonstruksi Rekapitulasi SKPD Induk
        ksort($simgajiSkpdMap);
        $rekapSkpd = [];
        $matchedSimpegSkpds = [];

        foreach ($simgajiSkpdMap as $kd => $item) {
            arsort($item['simpeg_skpds']);
            $dominanSimpeg = key($item['simpeg_skpds']) ?? '-';
            $jmlSimpeg = $dominanSimpeg !== '-' ? ($simpegSkpdCounts[$dominanSimpeg] ?? 0) : 0;
            if ($dominanSimpeg !== '-') {
                $matchedSimpegSkpds[$dominanSimpeg] = true;
            }

            $isDisdikBranch = in_array($kd, ['070', '071', '072', '073', '074', '075', '076', '077', '078', '079', '080', '081', '082']);
            $status = 'sesuai';
            if ($isDisdikBranch) {
                $status = 'cabang_wilayah';
            } elseif ($dominanSimpeg === '-' || empty($item['simpeg_skpds'])) {
                $status = 'tidak_terpetakan';
            } elseif ($item['jml_simgaji'] !== $jmlSimpeg) {
                $status = 'beda_jumlah';
            }

            $rekapSkpd[] = [
                'kdskpd' => $kd,
                'nama_simgaji' => $item['nama_simgaji'],
                'nama_simpeg' => $dominanSimpeg,
                'jml_simgaji' => $item['jml_simgaji'],
                'jml_simpeg' => $jmlSimpeg,
                'selisih' => $item['jml_simgaji'] - $jmlSimpeg,
                'satker_count' => count($item['satkers']),
                'inputers' => implode(', ', array_slice(array_keys($item['inputers']), 0, 4)),
                'status' => $status,
                'tidak_di_simpeg' => $item['tidak_di_simpeg'],
            ];
        }

        // Tambahkan SKPD SIMPEG yang belum terpetakan ke kode SIMGAJI
        $allSimpegSkpds = UnitKerja::whereNotNull('skpd')->distinct()->pluck('skpd')->sort()->values();
        foreach ($allSimpegSkpds as $sSkpd) {
            if (! isset($matchedSimpegSkpds[$sSkpd])) {
                $rekapSkpd[] = [
                    'kdskpd' => '-',
                    'nama_simgaji' => 'BELUM TERDAFTAR DI KODE SIMGAJI',
                    'nama_simpeg' => $sSkpd,
                    'jml_simgaji' => 0,
                    'jml_simpeg' => $simpegSkpdCounts[$sSkpd] ?? 0,
                    'selisih' => 0 - ($simpegSkpdCounts[$sSkpd] ?? 0),
                    'satker_count' => 0,
                    'inputers' => '-',
                    'status' => 'belum_ada_di_simgaji',
                    'tidak_di_simpeg' => 0,
                ];
            }
        }

        // 6. Rekonstruksi Pemetaan Hierarki Satker & UPTD
        $pemetaanSatker = [];
        $multiUptSatkerCount = 0;

        foreach ($satkerMap as $sKey => $sItem) {
            arsort($sItem['simpeg_upts']);
            arsort($sItem['simpeg_satkers']);
            arsort($sItem['simpeg_skpds']);

            $topUpt = key($sItem['simpeg_upts']) ?? '-';
            $topSatker = key($sItem['simpeg_satkers']) ?? '-';
            $topSkpd = key($sItem['simpeg_skpds']) ?? '-';

            $uptCount = count($sItem['simpeg_upts']);
            $isMultiUpt = $uptCount > 1;

            if ($isMultiUpt) {
                $multiUptSatkerCount++;
                $statusSatker = 'multi_upt';
            } elseif ($topUpt !== '-') {
                $statusSatker = 'sesuai_upt';
            } elseif ($topSkpd !== '-') {
                $statusSatker = 'induk_skpd';
            } else {
                $statusSatker = 'belum_terpetakan';
            }

            $detailUpts = [];
            foreach ($sItem['simpeg_upts'] as $uName => $uCount) {
                $detailUpts[] = "$uName ($uCount peg)";
            }

            $pemetaanSatker[] = [
                'kdskpd' => $sItem['kdskpd'],
                'kdsatker' => $sItem['kdsatker'],
                'nama_skpd_simgaji' => $sItem['nama_skpd_simgaji'],
                'inputer' => implode(', ', array_slice(array_keys($sItem['inputers']), 0, 3)) ?: '-',
                'jml_simgaji' => $sItem['jml_simgaji'],
                'skpd_simpeg' => $topSkpd,
                'upt_simpeg' => $topUpt,
                'satker_simpeg' => $topSatker,
                'upt_count' => $uptCount,
                'detail_upts' => implode('; ', $detailUpts),
                'status_satker' => $statusSatker,
                'tidak_di_simpeg' => $sItem['tidak_di_simpeg'],
            ];
        }

        // Urutkan Satker berdasarkan Kode SKPD & Kode Satker
        usort($pemetaanSatker, function ($a, $b) {
            $cmp = strcmp($a['kdskpd'], $b['kdskpd']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($a['kdsatker'], $b['kdsatker']);
        });

        $result = [
            'rekap_skpd' => $rekapSkpd,
            'pemetaan_satker' => $pemetaanSatker,
            'beda_pegawai' => $bedaPegawai,
            'summary' => [
                'total_aktif_simgaji' => $totalAktifSimgaji,
                'total_skpd_simgaji' => count($simgajiSkpdMap),
                'total_skpd_simpeg' => count($allSimpegSkpds),
                'total_satker_simgaji' => count($satkerMap),
                'multi_upt_count' => $multiUptSatkerCount,
                'beda_pegawai_count' => count($bedaPegawai),
            ],
            'active_dbf' => $activeFile,
            'cached_at' => date('d/m/Y H:i').' WITA',
        ];

        Cache::put($cacheKey, $result, 86400);

        return $result;
    }

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'rekap_skpd');
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');

        $data = $this->getPenyelarasanData();

        if (isset($data['error'])) {
            return view('laporan.penyelarasan_unit.index', [
                'error' => $data['error'],
                'activeTab' => $activeTab,
                'summary' => [],
                'paginatedItems' => new LengthAwarePaginator([], 0, 20),
                'allSkpds' => [],
            ]);
        }

        $items = collect($data[$activeTab] ?? []);

        // Filter berdasarkan Status
        if ($statusFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['status'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['status_satker'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $statusFilter);
            }
        }

        // Filter berdasarkan SKPD
        if ($skpdFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['nama_simpeg'] ?? '') === $skpdFilter || ($item['nama_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['nama_skpd_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['skpd_simgaji'] ?? '') === $skpdFilter);
            }
        }

        // Filter Search Box
        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower, $activeTab) {
                if ($activeTab === 'rekap_skpd') {
                    return str_contains(strtolower($item['kdskpd'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['inputers'] ?? ''), $searchLower);
                } elseif ($activeTab === 'pemetaan_satker') {
                    return str_contains(strtolower($item['kdskpd'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['kdsatker'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_skpd_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['upt_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['satker_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['inputer'] ?? ''), $searchLower);
                } elseif ($activeTab === 'beda_pegawai') {
                    return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['skpd_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['upt_simpeg'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['kdsatker_simgaji'] ?? ''), $searchLower);
                }

                return false;
            });
        }

        // Pagination
        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedItems = new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $allSkpds = UnitKerja::whereNotNull('skpd')->distinct()->pluck('skpd')->sort()->values();

        return view('laporan.penyelarasan_unit.index', [
            'activeTab' => $activeTab,
            'summary' => $data['summary'],
            'paginatedItems' => $paginatedItems,
            'allSkpds' => $allSkpds,
            'activeFile' => $data['active_dbf'],
            'cachedAt' => $data['cached_at'],
            'search' => $search,
            'statusFilter' => $statusFilter,
            'skpdFilter' => $skpdFilter,
        ]);
    }

    public function refreshCache()
    {
        Cache::forget('penyelarasan_unit_kerja_data_v2');
        $this->getPenyelarasanData(true);

        return redirect()->back()->with('success', 'Data penyelarasan unit kerja (SIMGAJI vs SIMPEG) berhasil diperbarui dan dikalkulasi ulang.');
    }

    public function exportExcel(Request $request)
    {
        $activeTab = $request->get('tab', 'rekap_skpd');
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');

        $data = $this->getPenyelarasanData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data[$activeTab] ?? []);

        if ($statusFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['status'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['status_satker'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $statusFilter);
            }
        }

        if ($skpdFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['nama_simpeg'] ?? '') === $skpdFilter || ($item['nama_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['nama_skpd_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['skpd_simgaji'] ?? '') === $skpdFilter);
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower, $activeTab) {
                if ($activeTab === 'rekap_skpd') {
                    return str_contains(strtolower($item['kdskpd'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simpeg'] ?? ''), $searchLower);
                } elseif ($activeTab === 'pemetaan_satker') {
                    return str_contains(strtolower($item['kdsatker'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_skpd_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['upt_simpeg'] ?? ''), $searchLower);
                } elseif ($activeTab === 'beda_pegawai') {
                    return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama'] ?? ''), $searchLower);
                }

                return false;
            });
        }

        $tabTitles = [
            'rekap_skpd' => 'Rekapitulasi SKPD Induk',
            'pemetaan_satker' => 'Pemetaan UPTD & SATKER',
            'beda_pegawai' => 'Daftar Pegawai Beda Penempatan',
        ];
        $tabTitle = $tabTitles[$activeTab] ?? 'Penyelarasan Unit Kerja';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $cleanTitle = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $tabTitle), 0, 31);
        $sheet->setTitle($cleanTitle ?: 'Penyelarasan');

        // Header Dokumen
        $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet->setCellValue('A2', 'LAPORAN PENYELARASAN SKPD -- UPTD -- SATKER (SIMGAJI VS SIMPEG)');
        $sheet->setCellValue('A3', 'Kategori: '.$tabTitle.' | Database Acuan: '.($data['active_dbf']['filename'] ?? '-').' | Diunduh: '.date('d/m/Y H:i').' WITA');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        $rowNum = 5;
        $headers = [];

        if ($activeTab === 'rekap_skpd') {
            $headers = ['No', 'Kode SKPD (SIMGAJI)', 'Nama SKPD di SIMGAJI', 'Nama SKPD di SIMPEG (Aplikasi)', 'Pegawai SIMGAJI', 'Pegawai SIMPEG', 'Selisih', 'Jml Satker SIMGAJI', 'Operator/Inputer', 'Status Kesesuaian'];
        } elseif ($activeTab === 'pemetaan_satker') {
            $headers = ['No', 'Kode SKPD', 'Kode Satker SIMGAJI', 'Nama SKPD SIMGAJI', 'Operator/Inputer', 'Pegawai SIMGAJI', 'SKPD di SIMPEG', 'UPTD di SIMPEG', 'Satker di SIMPEG', 'Status Pemetaan'];
        } else {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD SIMPEG', 'UPTD SIMPEG', 'Satker SIMPEG', 'Kode SKPD SIMGAJI', 'SKPD SIMGAJI', 'Kode Satker SIMGAJI', 'Inputer SIMGAJI', 'Jenis Selisih', 'Keterangan Analisis'];
        }

        $colLetter = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($colLetter.$rowNum, $h);
            $sheet->getStyle($colLetter.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle($colLetter.$rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF1F5F9');
            $sheet->getStyle($colLetter.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colLetter++;
        }

        $rowNum++;
        $no = 1;

        foreach ($items as $item) {
            $sheet->setCellValue('A'.$rowNum, $no++);

            if ($activeTab === 'rekap_skpd') {
                $sheet->setCellValueExplicit('B'.$rowNum, (string) ($item['kdskpd'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$rowNum, $item['nama_simgaji'] ?? '-');
                $sheet->setCellValue('D'.$rowNum, $item['nama_simpeg'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, (int) ($item['jml_simgaji'] ?? 0));
                $sheet->setCellValue('F'.$rowNum, (int) ($item['jml_simpeg'] ?? 0));
                $sheet->setCellValue('G'.$rowNum, (int) ($item['selisih'] ?? 0));
                $sheet->setCellValue('H'.$rowNum, (int) ($item['satker_count'] ?? 0));
                $sheet->setCellValue('I'.$rowNum, $item['inputers'] ?? '-');

                $statusLabel = match ($item['status'] ?? '') {
                    'sesuai' => 'Sesuai (Matched)',
                    'beda_jumlah' => 'Selisih Jumlah Pegawai',
                    'cabang_wilayah' => 'Pecahan Cabang Disdik SIMGAJI',
                    'tidak_terpetakan' => 'Belum Terpetakan di SIMPEG',
                    'belum_ada_di_simgaji' => 'Belum Ada di SIMGAJI',
                    default => $item['status'] ?? '-'
                };
                $sheet->setCellValue('J'.$rowNum, $statusLabel);
            } elseif ($activeTab === 'pemetaan_satker') {
                $sheet->setCellValueExplicit('B'.$rowNum, (string) ($item['kdskpd'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C'.$rowNum, (string) ($item['kdsatker'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('D'.$rowNum, $item['nama_skpd_simgaji'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['inputer'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, (int) ($item['jml_simgaji'] ?? 0));
                $sheet->setCellValue('G'.$rowNum, $item['skpd_simpeg'] ?? '-');
                $sheet->setCellValue('H'.$rowNum, $item['upt_simpeg'] ?? '-');
                $sheet->setCellValue('I'.$rowNum, $item['satker_simpeg'] ?? '-');

                $statusLabel = match ($item['status_satker'] ?? '') {
                    'sesuai_upt' => 'Terpetakan ke 1 UPTD',
                    'multi_upt' => 'Bercampur >1 UPTD SIMPEG',
                    'induk_skpd' => 'Satker Tingkat Induk',
                    'belum_terpetakan' => 'Belum Ada di SIMPEG',
                    default => $item['status_satker'] ?? '-'
                };
                $sheet->setCellValue('J'.$rowNum, $statusLabel);
            } else {
                $sheet->setCellValueExplicit('B'.$rowNum, (string) ($item['nip'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$rowNum, $item['nama'] ?? '-');
                $sheet->setCellValue('D'.$rowNum, $item['skpd_simpeg'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['upt_simpeg'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['satker_simpeg'] ?? '-');
                $sheet->setCellValueExplicit('G'.$rowNum, (string) ($item['kdskpd_simgaji'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('H'.$rowNum, $item['skpd_simgaji'] ?? '-');
                $sheet->setCellValueExplicit('I'.$rowNum, (string) ($item['kdsatker_simgaji'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('J'.$rowNum, $item['inputer_simgaji'] ?? '-');
                $sheet->setCellValue('K'.$rowNum, $item['jenis_selisih'] ?? '-');
                $sheet->setCellValue('L'.$rowNum, $item['keterangan'] ?? '-');
            }

            $rowNum++;
        }

        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A5:'.$lastCol.($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Penyelarasan_SKPD_UPTD_'.str_replace(' ', '_', $tabTitle).'_'.date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $activeTab = $request->get('tab', 'rekap_skpd');
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', 'semua');
        $skpdFilter = $request->get('skpd', 'semua');

        $data = $this->getPenyelarasanData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data[$activeTab] ?? []);

        if ($statusFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['status'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['status_satker'] ?? '') === $statusFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['jenis_selisih'] ?? '') === $statusFilter);
            }
        }

        if ($skpdFilter !== 'semua') {
            if ($activeTab === 'rekap_skpd') {
                $items = $items->filter(fn ($item) => ($item['nama_simpeg'] ?? '') === $skpdFilter || ($item['nama_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'pemetaan_satker') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['nama_skpd_simgaji'] ?? '') === $skpdFilter);
            } elseif ($activeTab === 'beda_pegawai') {
                $items = $items->filter(fn ($item) => ($item['skpd_simpeg'] ?? '') === $skpdFilter || ($item['skpd_simgaji'] ?? '') === $skpdFilter);
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower, $activeTab) {
                if ($activeTab === 'rekap_skpd') {
                    return str_contains(strtolower($item['kdskpd'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_simpeg'] ?? ''), $searchLower);
                } elseif ($activeTab === 'pemetaan_satker') {
                    return str_contains(strtolower($item['kdsatker'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama_skpd_simgaji'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['upt_simpeg'] ?? ''), $searchLower);
                } elseif ($activeTab === 'beda_pegawai') {
                    return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['nama'] ?? ''), $searchLower);
                }

                return false;
            });
        }

        $tabTitles = [
            'rekap_skpd' => 'Rekapitulasi SKPD Induk (SIMGAJI vs SIMPEG)',
            'pemetaan_satker' => 'Pemetaan UPTD & Satuan Kerja (SIMGAJI vs SIMPEG)',
            'beda_pegawai' => 'Daftar Pegawai Beda Penempatan Unit Kerja',
        ];
        $tabTitle = $tabTitles[$activeTab] ?? 'Penyelarasan Unit Kerja';

        $pdf = Pdf::loadView('laporan.penyelarasan_unit.pdf', [
            'items' => $items,
            'activeTab' => $activeTab,
            'tabTitle' => $tabTitle,
            'activeFile' => $data['active_dbf'],
            'summary' => $data['summary'],
            'statusFilter' => $statusFilter,
            'skpdFilter' => $skpdFilter,
            'search' => $search,
        ])->setPaper('a4', 'landscape');

        $filename = 'Penyelarasan_Unit_'.date('Ymd_His').'.pdf';

        return $pdf->stream($filename);
    }
}
