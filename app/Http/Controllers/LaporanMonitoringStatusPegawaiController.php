<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnmatchedNip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use XBase\TableReader;

class LaporanMonitoringStatusPegawaiController extends Controller
{
    private array $mapPangkat = [
        '1A' => 'I/a', '1B' => 'I/b', '1C' => 'I/c', '1D' => 'I/d',
        '2A' => 'II/a', '2B' => 'II/b', '2C' => 'II/c', '2D' => 'II/d',
        '3A' => 'III/a', '3B' => 'III/b', '3C' => 'III/c', '3D' => 'III/d',
        '4A' => 'IV/a', '4B' => 'IV/b', '4C' => 'IV/c', '4D' => 'IV/d', '4E' => 'IV/e',
        '01' => 'I', '1' => 'I', '02' => 'II', '2' => 'II', '03' => 'III', '3' => 'III',
        '04' => 'IV', '4' => 'IV', '05' => 'V', '5' => 'V', '06' => 'VI', '6' => 'VI',
        '07' => 'VII', '7' => 'VII', '08' => 'VIII', '8' => 'VIII', '09' => 'IX', '9' => 'IX',
        '10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII', '14' => 'XIV',
        '15' => 'XV', '16' => 'XVI', '17' => 'XVII',
    ];

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
        '070' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '071' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '072' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '073' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '074' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '075' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '076' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '077' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '078' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '079' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '080' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '081' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '082' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        '100' => 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN',
        '101' => 'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
        '102' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
        '103' => 'DINAS PERINDUSTRIAN',
    ];

    /**
     * Dapatkan File DBF Aktif (MST_PGW)
     */
    private function getActiveDbfFile(): ?array
    {
        return app(RekonsiliasiSimgajiController::class)->getActiveDbfFile('mst_pgw');
    }

    /**
     * Ambil dan olah data status kepegawaian dari master SIMGAJI disandingkan dengan Master SIMPEG & transaksi pembayaran.
     */
    public function getMonitoringData(bool $forceRefresh = false): array
    {
        $cacheKey = 'laporan_monitoring_status_pegawai_cache_v1';
        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $activeFile = $this->getActiveDbfFile();
        if (! $activeFile || ! file_exists($activeFile['path'])) {
            return [
                'error' => 'File Master SIMGAJI (MST_PGW) tidak ditemukan. Silakan unggah database melalui menu Upload Master SIMGAJI.',
            ];
        }

        // 1. Preload Pegawai SIMPEG & Unit Kerja
        $dbPegawais = Pegawai::with(['unitKerja:id,skpd,satker', 'jabatan:id,nama'])
            ->select('id', 'nip', 'nama', 'golru', 'status_pegawai', 'unit_kerja_id', 'jabatan_id')
            ->get()
            ->keyBy('nip');

        // 2. Preload Transaksi Realisasi TPP & Gaji per Pegawai
        $tppByPegawai = RealisasiTpp::select('pegawai_id', 'periode', 'nominal_bersih', 'created_at')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('pegawai_id');

        $gajiByPegawai = RealisasiGaji::select('pegawai_id', 'periode', 'gaji_bersih', 'created_at')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('pegawai_id');

        // 3. Preload Unmatched NIP log
        $unmatchedLogs = UnmatchedNip::all()->groupBy('nip');

        $today = date('Y-m-d');
        $currentYear = (int) date('Y');

        $skpdRekap = [];
        $nominatifNonAktif = [];
        $proyeksiPensiun = [];
        $anomaliPenggajian = [];

        $totalRecords = 0;
        $countPnsAktif = 0;
        $countPppkAktif = 0;
        $countPensiunBup = 0;
        $countPensiunSendiri = 0;
        $countMeninggal = 0;
        $countPindah = 0;
        $countKeluar = 0;
        $countCuti = 0;
        $countMpp = 0;
        $countPejabat = 0;
        $countProyeksi12Bln = 0;

        try {
            $table = new TableReader($activeFile['path'], ['encoding' => 'cp850']);
            $totalRecords = $table->getRecordCount();

            while ($r = $table->nextRecord()) {
                $nip = trim((string) $r->get('nip'));
                if (! $nip) {
                    continue;
                }

                $nama = trim((string) $r->get('nama'));
                $kdstapeg = trim((string) $r->get('kdstapeg'));
                $tmtstop = trim((string) $r->get('tmtstop'));
                $kdskpd = trim((string) $r->get('kdskpd'));
                $kdpangkat = trim((string) $r->get('kdpangkat'));
                $pangkatConv = $this->mapPangkat[strtoupper($kdpangkat)] ?? $kdpangkat;
                $catatan = trim((string) $r->get('catatan'));
                $bup = (int) trim((string) $r->get('bup'));
                $tgllhr = trim((string) $r->get('tgllhr'));

                $skpdNama = $this->skpdCodeMap[$kdskpd] ?? ($kdskpd ? "SKPD KODE {$kdskpd}" : 'SKPD TIDAK TERDEFINISI');

                // Inisialisasi SKPD Rekap
                if (! isset($skpdRekap[$skpdNama])) {
                    $skpdRekap[$skpdNama] = [
                        'skpd' => $skpdNama,
                        'kdskpd' => $kdskpd,
                        'pns_aktif' => 0,
                        'pppk_aktif' => 0,
                        'pensiun_bup' => 0,
                        'pensiun_sendiri' => 0,
                        'meninggal' => 0,
                        'pindah' => 0,
                        'keluar' => 0,
                        'cuti_cltn' => 0,
                        'mpp' => 0,
                        'proyeksi_pensiun' => 0,
                        'total' => 0,
                    ];
                }
                $skpdRekap[$skpdNama]['total']++;

                $isStopDate = (! empty($tmtstop) && $tmtstop !== '0000-00-00' && $tmtstop <= $today);

                // Klasifikasi Status Kepegawaian
                $statusKey = 'aktif';
                $statusLabel = 'Aktif';
                $badgeColor = 'badge-success';

                if ($kdstapeg === '23' || ($isStopDate && in_array($kdstapeg, ['4', '12', '13', '3']))) {
                    $statusKey = '23';
                    $statusLabel = 'Pensiun (BUP)';
                    $badgeColor = 'badge-danger';
                    $countPensiunBup++;
                    $skpdRekap[$skpdNama]['pensiun_bup']++;
                } elseif ($kdstapeg === '22') {
                    $statusKey = '22';
                    $statusLabel = 'Pensiun Sendiri / Stop Sementara';
                    $badgeColor = 'badge-warning';
                    $countPensiunSendiri++;
                    $skpdRekap[$skpdNama]['pensiun_sendiri']++;
                } elseif ($kdstapeg === '27') {
                    $statusKey = '27';
                    $statusLabel = 'Meninggal Dunia';
                    $badgeColor = 'badge-dark';
                    $countMeninggal++;
                    $skpdRekap[$skpdNama]['meninggal']++;
                } elseif ($kdstapeg === '28') {
                    $statusKey = '28';
                    $statusLabel = 'Pindah Instansi';
                    $badgeColor = 'badge-info';
                    $countPindah++;
                    $skpdRekap[$skpdNama]['pindah']++;
                } elseif ($kdstapeg === '24') {
                    $statusKey = '24';
                    $statusLabel = 'Berhenti / Keluar';
                    $badgeColor = 'badge-secondary';
                    $countKeluar++;
                    $skpdRekap[$skpdNama]['keluar']++;
                } elseif ($kdstapeg === '6') {
                    $statusKey = '6';
                    $statusLabel = 'Cuti di Luar Tanggungan Negara (CLTN)';
                    $badgeColor = 'badge-purple';
                    $countCuti++;
                    $skpdRekap[$skpdNama]['cuti_cltn']++;
                } elseif ($kdstapeg === '9') {
                    $statusKey = '9';
                    $statusLabel = 'Masa Persiapan Pensiun (MPP)';
                    $badgeColor = 'badge-amber';
                    $countMpp++;
                    $skpdRekap[$skpdNama]['mpp']++;
                } elseif ($kdstapeg === '1') {
                    $statusKey = '1';
                    $statusLabel = 'Pejabat Negara';
                    $badgeColor = 'badge-primary';
                    $countPejabat++;
                    $skpdRekap[$skpdNama]['pns_aktif']++;
                } elseif ($kdstapeg === '12' || $kdstapeg === '13') {
                    $statusKey = '12';
                    $statusLabel = ($kdstapeg === '13') ? 'PPPK Paruh Waktu' : 'PPPK Penuh Waktu';
                    $badgeColor = 'badge-emerald';
                    $countPppkAktif++;
                    $skpdRekap[$skpdNama]['pppk_aktif']++;
                } else {
                    $statusKey = '4';
                    $statusLabel = 'PNS Aktif';
                    $badgeColor = 'badge-blue';
                    $countPnsAktif++;
                    $skpdRekap[$skpdNama]['pns_aktif']++;
                }

                // Cek data referensi SIMPEG
                $simpegPgw = $dbPegawais[$nip] ?? null;

                // Masukkan ke nominatif jika non-aktif / pensiun / mutasi
                if (in_array($statusKey, ['23', '22', '27', '28', '24', '6', '9'])) {
                    $nominatifNonAktif[] = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'golru' => $pangkatConv,
                        'skpd' => $skpdNama,
                        'kdskpd' => $kdskpd,
                        'kdstapeg' => $kdstapeg,
                        'status_key' => $statusKey,
                        'status_label' => $statusLabel,
                        'badge_color' => $badgeColor,
                        'tmtstop' => $tmtstop,
                        'catatan' => $catatan,
                        'ada_di_simpeg' => $simpegPgw !== null,
                    ];
                }

                // Cek Proyeksi Pensiun Mendatang (Aktif dan akan pensiun dalam 1-2 tahun ke depan)
                if (in_array($statusKey, ['4', '12', '13', '3']) && ! empty($tmtstop) && $tmtstop > $today) {
                    $tmtYear = (int) substr($tmtstop, 0, 4);
                    if ($tmtYear <= $currentYear + 2) {
                        $skpdRekap[$skpdNama]['proyeksi_pensiun']++;
                        if ($tmtYear <= $currentYear + 1) {
                            $countProyeksi12Bln++;
                        }

                        $proyeksiPensiun[] = [
                            'nip' => $nip,
                            'nama' => $nama,
                            'golru' => $pangkatConv,
                            'skpd' => $skpdNama,
                            'status_asn' => ($kdstapeg === '12' || $kdstapeg === '13') ? 'PPPK' : 'PNS',
                            'tmt_pensiun' => $tmtstop,
                            'tahun' => $tmtYear,
                            'bup' => $bup ?: 58,
                            'tgllhr' => $tgllhr,
                            'catatan' => $catatan,
                        ];
                    }
                }

                // Cek Anomali Penggajian Pasca TMT Stop
                // Kondisi: Pegawai berstatus pensiun/meninggal/keluar di SIMGAJI, namun masih tercatat ada transaksi pembayaran atau log upload
                if (in_array($statusKey, ['23', '27', '28', '24', '22']) && $isStopDate) {
                    $hasTpp = false;
                    $hasGaji = false;
                    $hasUnmatched = isset($unmatchedLogs[$nip]);
                    $tppTx = null;
                    $gajiTx = null;

                    if ($simpegPgw) {
                        $tppList = $tppByPegawai[$simpegPgw->id] ?? null;
                        if ($tppList && $tppList->isNotEmpty()) {
                            $tppTx = $tppList->first();
                            $hasTpp = true;
                        }

                        $gajiList = $gajiByPegawai[$simpegPgw->id] ?? null;
                        if ($gajiList && $gajiList->isNotEmpty()) {
                            $gajiTx = $gajiList->first();
                            $hasGaji = true;
                        }
                    }

                    if ($hasTpp || $hasGaji || $hasUnmatched) {
                        $alasanAnomali = [];
                        if ($hasUnmatched) {
                            $alasanAnomali[] = 'Gagal upload (NIP tidak ditemukan di SIMPEG saat impor TPP/Gaji)';
                        }
                        if ($hasTpp) {
                            $alasanAnomali[] = 'Ada histori pembayaran TPP (Periode: '.$tppTx->periode.')';
                        }
                        if ($hasGaji) {
                            $alasanAnomali[] = 'Ada histori pembayaran Gaji (Periode: '.$gajiTx->periode.')';
                        }

                        $anomaliPenggajian[] = [
                            'nip' => $nip,
                            'nama' => $nama,
                            'skpd' => $skpdNama,
                            'status_simgaji' => $statusLabel,
                            'badge_color' => $badgeColor,
                            'tmtstop' => $tmtstop,
                            'catatan' => $catatan,
                            'indikasi' => implode(' • ', $alasanAnomali),
                            'ada_unmatched' => $hasUnmatched,
                            'ada_tpp' => $hasTpp,
                            'ada_gaji' => $hasGaji,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return [
                'error' => 'Gagal membaca database master SIMGAJI: '.$e->getMessage(),
            ];
        }

        // Urutkan data rekap per SKPD
        ksort($skpdRekap);

        // Urutkan proyeksi berdasarkan tanggal pensiun terdekat
        usort($proyeksiPensiun, fn ($a, $b) => strcmp($a['tmt_pensiun'], $b['tmt_pensiun']));

        $result = [
            'summary' => [
                'total_records' => $totalRecords,
                'pns_aktif' => $countPnsAktif,
                'pppk_aktif' => $countPppkAktif,
                'pensiun_bup' => $countPensiunBup,
                'pensiun_sendiri' => $countPensiunSendiri,
                'meninggal' => $countMeninggal,
                'pindah' => $countPindah,
                'keluar' => $countKeluar,
                'cuti' => $countCuti,
                'mpp' => $countMpp,
                'proyeksi_12_bulan' => $countProyeksi12Bln,
                'total_anomali' => count($anomaliPenggajian),
                'active_file_name' => $activeFile['filename'] ?? '-',
            ],
            'skpdRekap' => array_values($skpdRekap),
            'nominatifNonAktif' => $nominatifNonAktif,
            'proyeksiPensiun' => $proyeksiPensiun,
            'anomaliPenggajian' => $anomaliPenggajian,
        ];

        Cache::put($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * Tampilkan halaman utama Laporan Monitoring Status Pegawai.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'rekap');
        $skpdFilter = $request->get('skpd');
        $statusFilter = $request->get('status', 'semua');
        $search = trim((string) $request->get('search', ''));
        $tahunProyeksi = $request->get('tahun', 'semua');

        $data = $this->getMonitoringData();

        if (isset($data['error'])) {
            return view('laporan.monitoring_status.index', [
                'error' => $data['error'],
                'summary' => [],
                'tab' => $tab,
            ]);
        }

        $summary = $data['summary'];
        $skpdRekap = collect($data['skpdRekap']);
        $nominatifCollection = collect($data['nominatifNonAktif']);
        $proyeksiCollection = collect($data['proyeksiPensiun']);
        $anomaliCollection = collect($data['anomaliPenggajian']);

        // Filter Rekap SKPD
        if ($search) {
            $skpdRekap = $skpdRekap->filter(fn ($item) => str_contains(strtolower($item['skpd']), strtolower($search)));
        }

        // Filter Nominatif Non-Aktif
        if ($skpdFilter) {
            $nominatifCollection = $nominatifCollection->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            $proyeksiCollection = $proyeksiCollection->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            $anomaliCollection = $anomaliCollection->filter(fn ($item) => $item['skpd'] === $skpdFilter);
        }

        if ($statusFilter && $statusFilter !== 'semua') {
            $nominatifCollection = $nominatifCollection->filter(fn ($item) => $item['status_key'] === $statusFilter);
        }

        if ($search) {
            $searchLower = strtolower($search);
            $nominatifCollection = $nominatifCollection->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip']), $searchLower)
                    || str_contains(strtolower($item['nama']), $searchLower)
                    || str_contains(strtolower($item['skpd']), $searchLower);
            });

            $proyeksiCollection = $proyeksiCollection->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip']), $searchLower)
                    || str_contains(strtolower($item['nama']), $searchLower)
                    || str_contains(strtolower($item['skpd']), $searchLower);
            });

            $anomaliCollection = $anomaliCollection->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip']), $searchLower)
                    || str_contains(strtolower($item['nama']), $searchLower)
                    || str_contains(strtolower($item['skpd']), $searchLower);
            });
        }

        if ($tahunProyeksi && $tahunProyeksi !== 'semua') {
            $proyeksiCollection = $proyeksiCollection->filter(fn ($item) => (string) $item['tahun'] === (string) $tahunProyeksi);
        }

        // Paginasi untuk masing-masing tab
        $perPage = (int) $request->get('per_page', 50);
        if (! in_array($perPage, [25, 50, 100, 250, 500])) {
            $perPage = 50;
        }

        $activeCount = match ($tab) {
            'nominatif' => $nominatifCollection->count(),
            'proyeksi' => $proyeksiCollection->count(),
            'anomali' => $anomaliCollection->count(),
            default => 0,
        };

        $page = (int) $request->get('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($activeCount > 0) {
            $maxPage = (int) ceil($activeCount / $perPage);
            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $paginatedNominatif = new LengthAwarePaginator(
            $nominatifCollection->slice(($page - 1) * $perPage, $perPage)->values(),
            $nominatifCollection->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $paginatedProyeksi = new LengthAwarePaginator(
            $proyeksiCollection->slice(($page - 1) * $perPage, $perPage)->values(),
            $proyeksiCollection->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $paginatedAnomali = new LengthAwarePaginator(
            $anomaliCollection->slice(($page - 1) * $perPage, $perPage)->values(),
            $anomaliCollection->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $availableSkpds = collect($data['skpdRekap'])->pluck('skpd')->sort()->values();

        return view('laporan.monitoring_status.index', [
            'summary' => $summary,
            'tab' => $tab,
            'skpdRekap' => $skpdRekap,
            'paginatedNominatif' => $paginatedNominatif,
            'paginatedProyeksi' => $paginatedProyeksi,
            'paginatedAnomali' => $paginatedAnomali,
            'availableSkpds' => $availableSkpds,
            'skpdFilter' => $skpdFilter,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'tahunProyeksi' => $tahunProyeksi,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Segarkan cache data monitoring status kepegawaian.
     */
    public function refreshCache()
    {
        $this->getMonitoringData(true);

        return redirect()->back()->with('success', 'Data Monitoring Status Pegawai & Pensiun berhasil diperbarui dari master database SIMGAJI.');
    }

    /**
     * Ekspor Laporan Monitoring Status Pegawai ke Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $tab = $request->get('tab', 'rekap');
        $skpdFilter = $request->get('skpd');
        $statusFilter = $request->get('status', 'semua');
        $search = trim((string) $request->get('search', ''));
        $tahunProyeksi = $request->get('tahun', 'semua');

        $data = $this->getMonitoringData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tab === 'rekap') {
            $sheet->setTitle('Rekap Status SKPD');
            $sheet->mergeCells('A1:K1');
            $sheet->setCellValue('A1', 'REKAPITULASI STATUS KEPEGAWAIAN & PENSIUN PER SKPD');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:K2');
            $sheet->setCellValue('A2', 'SUMBER DATA: DATABASE MASTER SIMGAJI (TASPEN) • TANGGAL CETAK: '.date('d M Y'));
            $sheet->getStyle('A2')->getFont()->setSize(10);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headers = [
                'NO', 'NAMA SKPD / UNIT KERJA', 'PNS AKTIF', 'PPPK AKTIF', 'PENSIUN BUP',
                'MENINGGAL', 'PINDAH', 'KELUAR', 'CUTI / MPP', 'PROYEKSI PENSIUN', 'TOTAL PEGAWAI',
            ];

            $sheet->fromArray($headers, null, 'A4');
            $sheet->getStyle('A4:K4')->getFont()->setBold(true);
            $sheet->getStyle('A4:K4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle('A4:K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum = 5;
            $no = 1;
            $items = collect($data['skpdRekap']);
            if ($search) {
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['skpd']), strtolower($search)));
            }

            foreach ($items as $row) {
                $cutiMpp = $row['cuti_cltn'] + $row['mpp'] + $row['pensiun_sendiri'];
                $sheet->setCellValue('A'.$rowNum, $no++);
                $sheet->setCellValue('B'.$rowNum, $row['skpd']);
                $sheet->setCellValue('C'.$rowNum, $row['pns_aktif']);
                $sheet->setCellValue('D'.$rowNum, $row['pppk_aktif']);
                $sheet->setCellValue('E'.$rowNum, $row['pensiun_bup']);
                $sheet->setCellValue('F'.$rowNum, $row['meninggal']);
                $sheet->setCellValue('G'.$rowNum, $row['pindah']);
                $sheet->setCellValue('H'.$rowNum, $row['keluar']);
                $sheet->setCellValue('I'.$rowNum, $cutiMpp);
                $sheet->setCellValue('J'.$rowNum, $row['proyeksi_pensiun']);
                $sheet->setCellValue('K'.$rowNum, $row['total']);

                $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C'.$rowNum.':K'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $rowNum++;
            }

            $lastDataRow = $rowNum - 1;
            $sheet->setCellValue('A'.$rowNum, 'TOTAL KESELURUHAN');
            $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
            $sheet->setCellValue('C'.$rowNum, "=SUM(C5:C{$lastDataRow})");
            $sheet->setCellValue('D'.$rowNum, "=SUM(D5:D{$lastDataRow})");
            $sheet->setCellValue('E'.$rowNum, "=SUM(E5:E{$lastDataRow})");
            $sheet->setCellValue('F'.$rowNum, "=SUM(F5:F{$lastDataRow})");
            $sheet->setCellValue('G'.$rowNum, "=SUM(G5:G{$lastDataRow})");
            $sheet->setCellValue('H'.$rowNum, "=SUM(H5:H{$lastDataRow})");
            $sheet->setCellValue('I'.$rowNum, "=SUM(I5:I{$lastDataRow})");
            $sheet->setCellValue('J'.$rowNum, "=SUM(J5:J{$lastDataRow})");
            $sheet->setCellValue('K'.$rowNum, "=SUM(K5:K{$lastDataRow})");
            $sheet->getStyle('A'.$rowNum.':K'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNum.':K'.$rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

            $sheet->getStyle("A4:K{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } elseif ($tab === 'nominatif') {
            $sheet->setTitle('Nominatif Non-Aktif');
            $sheet->mergeCells('A1:H1');
            $sheet->setCellValue('A1', 'DAFTAR NOMINATIF PEGAWAI NON-AKTIF (PENSIUN, MENINGGAL, PINDAH, KELUAR)');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headers = ['NO', 'NIP', 'NAMA PEGAWAI', 'GOLRU', 'SKPD / UNIT KERJA', 'STATUS KEPEGAWAIAN', 'TMT STOP GAJI', 'CATATAN SK MUTASI'];
            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:H3')->getFont()->setBold(true);
            $sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $items = collect($data['nominatifNonAktif']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($statusFilter && $statusFilter !== 'semua') {
                $items = $items->filter(fn ($item) => $item['status_key'] === $statusFilter);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }

            $rowNum = 4;
            $no = 1;
            foreach ($items as $row) {
                $sheet->setCellValue('A'.$rowNum, $no++);
                $sheet->setCellValueExplicit('B'.$rowNum, $row['nip'], DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$rowNum, $row['nama']);
                $sheet->setCellValue('D'.$rowNum, $row['golru']);
                $sheet->setCellValue('E'.$rowNum, $row['skpd']);
                $sheet->setCellValue('F'.$rowNum, $row['status_label']);
                $sheet->setCellValue('G'.$rowNum, $row['tmtstop'] ?: '-');
                $sheet->setCellValue('H'.$rowNum, $row['catatan'] ?: '-');
                $rowNum++;
            }

            $sheet->getStyle('A3:H'.($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } elseif ($tab === 'proyeksi') {
            $sheet->setTitle('Proyeksi Pensiun');
            $sheet->mergeCells('A1:H1');
            $sheet->setCellValue('A1', 'DAFTAR PROYEKSI PEGAWAI MEMASUKI USIA PENSIUN');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headers = ['NO', 'NIP', 'NAMA PEGAWAI', 'JENIS ASN', 'GOLRU', 'SKPD / UNIT KERJA', 'BUP', 'TMT PENSIUN'];
            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:H3')->getFont()->setBold(true);
            $sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $items = collect($data['proyeksiPensiun']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($tahunProyeksi && $tahunProyeksi !== 'semua') {
                $items = $items->filter(fn ($item) => (string) $item['tahun'] === (string) $tahunProyeksi);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }

            $rowNum = 4;
            $no = 1;
            foreach ($items as $row) {
                $sheet->setCellValue('A'.$rowNum, $no++);
                $sheet->setCellValueExplicit('B'.$rowNum, $row['nip'], DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$rowNum, $row['nama']);
                $sheet->setCellValue('D'.$rowNum, $row['status_asn']);
                $sheet->setCellValue('E'.$rowNum, $row['golru']);
                $sheet->setCellValue('F'.$rowNum, $row['skpd']);
                $sheet->setCellValue('G'.$rowNum, $row['bup'].' Thn');
                $sheet->setCellValue('H'.$rowNum, $row['tmt_pensiun']);
                $rowNum++;
            }

            $sheet->getStyle('A3:H'.($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            // Anomali
            $sheet->setTitle('Anomali Penggajian');
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'MONITORING POTENSI ANOMALI PEMBAYARAN GAJI / TPP PASCA TMT STOP');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headers = ['NO', 'NIP', 'NAMA PEGAWAI', 'SKPD', 'STATUS SIMGAJI', 'TMT STOP', 'INDIKASI ANOMALI / KETERANGAN'];
            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:G3')->getFont()->setBold(true);
            $sheet->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $items = collect($data['anomaliPenggajian']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }

            $rowNum = 4;
            $no = 1;
            foreach ($items as $row) {
                $sheet->setCellValue('A'.$rowNum, $no++);
                $sheet->setCellValueExplicit('B'.$rowNum, $row['nip'], DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$rowNum, $row['nama']);
                $sheet->setCellValue('D'.$rowNum, $row['skpd']);
                $sheet->setCellValue('E'.$rowNum, $row['status_simgaji']);
                $sheet->setCellValue('F'.$rowNum, $row['tmtstop'] ?: '-');
                $sheet->setCellValue('G'.$rowNum, $row['indikasi']);
                $rowNum++;
            }

            $sheet->getStyle('A3:G'.($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $filename = 'Laporan_Monitoring_Status_Pegawai_'.strtoupper($tab).'_'.date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Ekspor Laporan Monitoring Status Pegawai ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->get('tab', 'rekap');
        $skpdFilter = $request->get('skpd');
        $statusFilter = $request->get('status', 'semua');
        $search = trim((string) $request->get('search', ''));
        $tahunProyeksi = $request->get('tahun', 'semua');

        $data = $this->getMonitoringData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $summary = $data['summary'];
        $items = collect($data['skpdRekap']);

        if ($tab === 'nominatif') {
            $items = collect($data['nominatifNonAktif']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($statusFilter && $statusFilter !== 'semua') {
                $items = $items->filter(fn ($item) => $item['status_key'] === $statusFilter);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }
        } elseif ($tab === 'proyeksi') {
            $items = collect($data['proyeksiPensiun']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($tahunProyeksi && $tahunProyeksi !== 'semua') {
                $items = $items->filter(fn ($item) => (string) $item['tahun'] === (string) $tahunProyeksi);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }
        } elseif ($tab === 'anomali') {
            $items = collect($data['anomaliPenggajian']);
            if ($skpdFilter) {
                $items = $items->filter(fn ($item) => $item['skpd'] === $skpdFilter);
            }
            if ($search) {
                $sLower = strtolower($search);
                $items = $items->filter(fn ($item) => str_contains(strtolower($item['nip']), $sLower) || str_contains(strtolower($item['nama']), $sLower));
            }
        }

        $pdf = Pdf::loadView('laporan.monitoring_status.pdf', [
            'summary' => $summary,
            'tab' => $tab,
            'items' => $items,
            'skpdFilter' => $skpdFilter,
            'statusFilter' => $statusFilter,
            'tahunProyeksi' => $tahunProyeksi,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Monitoring_Status_Pegawai_'.strtoupper($tab).'_'.date('Ymd').'.pdf');
    }
}
