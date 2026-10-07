<?php

namespace App\Http\Controllers;

use App\Models\AuditTunjanganResolusi;
use App\Models\Pegawai;
use App\Models\SimgajiKeluarga;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AuditTunjanganKeluargaController extends Controller
{
    /**
     * Cache key for audit data.
     */
    private const CACHE_KEY = 'audit_tunjangan_keluarga_data_v2';

    /**
     * Estimated average basic salary (Gapok) for financial impact projection.
     */
    private const ESTIMASI_GAPOK_RATA = 3700000;

    /**
     * Display the Audit Tunjangan Keluarga page.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'anak');
        $search = trim($request->query('q', ''));
        $skpdFilter = trim($request->query('skpd', ''));
        $statusFilter = $request->query('status', 'semua'); // 'semua', 'pending' (belum selesai), 'selesai'

        // Retrieve raw audit data from cache or database
        $auditData = $this->getAuditData();

        $dobelAnakAll = collect($auditData['dobel_anak']);
        $dobelPasanganAll = collect($auditData['dobel_pasangan']);
        $lebihKuotaAll = collect($auditData['lebih_kuota']);

        // List of SKPDs for dropdown filter
        $skpdList = UnitKerja::select('skpd')
            ->distinct()
            ->whereNotNull('skpd')
            ->where('skpd', '!=', '')
            ->orderBy('skpd')
            ->pluck('skpd');

        // Apply search, SKPD, and resolution status filters based on active tab
        if ($tab === 'pasangan') {
            $filtered = $dobelPasanganAll->filter(function ($item) use ($search, $skpdFilter, $statusFilter) {
                // Resolution status filter
                if ($statusFilter === 'pending' && $item['is_selesai']) {
                    return false;
                }
                if ($statusFilter === 'selesai' && ! $item['is_selesai']) {
                    return false;
                }

                $matchSearch = true;
                if ($search !== '') {
                    $searchLower = strtolower($search);
                    $matchSearch = str_contains(strtolower($item['nip_1']), $searchLower)
                        || str_contains(strtolower($item['nama_1']), $searchLower)
                        || str_contains(strtolower($item['nip_2']), $searchLower)
                        || str_contains(strtolower($item['nama_2']), $searchLower)
                        || str_contains(strtolower($item['no_sts'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['catatan'] ?? ''), $searchLower);
                }

                $matchSkpd = true;
                if ($skpdFilter !== '') {
                    $matchSkpd = ($item['skpd_1'] === $skpdFilter) || ($item['skpd_2'] === $skpdFilter);
                }

                return $matchSearch && $matchSkpd;
            });
        } elseif ($tab === 'kuota') {
            $filtered = $lebihKuotaAll->filter(function ($item) use ($search, $skpdFilter, $statusFilter) {
                // Resolution status filter
                if ($statusFilter === 'pending' && $item['is_selesai']) {
                    return false;
                }
                if ($statusFilter === 'selesai' && ! $item['is_selesai']) {
                    return false;
                }

                $matchSearch = true;
                if ($search !== '') {
                    $searchLower = strtolower($search);
                    $matchSearch = str_contains(strtolower($item['nip']), $searchLower)
                        || str_contains(strtolower($item['nama']), $searchLower)
                        || str_contains(strtolower($item['daftar_anak_str'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['no_sts'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['catatan'] ?? ''), $searchLower);
                }

                $matchSkpd = true;
                if ($skpdFilter !== '') {
                    $matchSkpd = ($item['skpd'] === $skpdFilter);
                }

                return $matchSearch && $matchSkpd;
            });
        } else {
            // Default: anak
            $filtered = $dobelAnakAll->filter(function ($item) use ($search, $skpdFilter, $statusFilter) {
                // Resolution status filter
                if ($statusFilter === 'pending' && $item['is_selesai']) {
                    return false;
                }
                if ($statusFilter === 'selesai' && ! $item['is_selesai']) {
                    return false;
                }

                $matchSearch = true;
                if ($search !== '') {
                    $searchLower = strtolower($search);
                    $matchSearch = str_contains(strtolower($item['nama_anak']), $searchLower)
                        || str_contains(strtolower($item['nip_1']), $searchLower)
                        || str_contains(strtolower($item['nama_1']), $searchLower)
                        || str_contains(strtolower($item['nip_2']), $searchLower)
                        || str_contains(strtolower($item['nama_2']), $searchLower)
                        || str_contains(strtolower($item['no_sts'] ?? ''), $searchLower)
                        || str_contains(strtolower($item['catatan'] ?? ''), $searchLower);
                }

                $matchSkpd = true;
                if ($skpdFilter !== '') {
                    $matchSkpd = ($item['skpd_1'] === $skpdFilter) || ($item['skpd_2'] === $skpdFilter);
                }

                return $matchSearch && $matchSkpd;
            });
        }

        // Pagination (20 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            $filtered->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Overall summary statistics
        $anakSelesaiCount = $dobelAnakAll->where('is_selesai', true)->count();
        $anakPendingCount = $dobelAnakAll->where('is_selesai', false)->count();

        $pasanganSelesaiCount = $dobelPasanganAll->where('is_selesai', true)->count();
        $pasanganPendingCount = $dobelPasanganAll->where('is_selesai', false)->count();

        $kuotaSelesaiCount = $lebihKuotaAll->where('is_selesai', true)->count();
        $kuotaPendingCount = $lebihKuotaAll->where('is_selesai', false)->count();

        $totalKelebihanAnak = $anakPendingCount * (self::ESTIMASI_GAPOK_RATA * 0.02);
        $totalKelebihanPasangan = $pasanganPendingCount * (self::ESTIMASI_GAPOK_RATA * 0.10);
        $totalKelebihanKuota = $lebihKuotaAll->where('is_selesai', false)->sum('kelebihan') * (self::ESTIMASI_GAPOK_RATA * 0.02);
        $totalEstimasiKelebihanBayar = $totalKelebihanAnak + $totalKelebihanPasangan + $totalKelebihanKuota;

        // Total refunds already deposited to Kasda with STS
        $totalStsDisetor = (float) AuditTunjanganResolusi::where('status', 'selesai')->sum('nominal_pengembalian');

        $stats = [
            'total_dobel_anak' => $dobelAnakAll->count(),
            'anak_selesai' => $anakSelesaiCount,
            'anak_pending' => $anakPendingCount,

            'total_dobel_pasangan' => $dobelPasanganAll->count(),
            'pasangan_selesai' => $pasanganSelesaiCount,
            'pasangan_pending' => $pasanganPendingCount,

            'total_lebih_kuota' => $lebihKuotaAll->count(),
            'kuota_selesai' => $kuotaSelesaiCount,
            'kuota_pending' => $kuotaPendingCount,

            'total_kasus_all' => $dobelAnakAll->count() + $dobelPasanganAll->count() + $lebihKuotaAll->count(),
            'total_selesai_all' => $anakSelesaiCount + $pasanganSelesaiCount + $kuotaSelesaiCount,
            'total_pending_all' => $anakPendingCount + $pasanganPendingCount + $kuotaPendingCount,

            'total_kelebihan_anak_rp' => $totalKelebihanAnak,
            'total_kelebihan_pasangan_rp' => $totalKelebihanPasangan,
            'total_kelebihan_kuota_rp' => $totalKelebihanKuota,
            'total_estimasi_rp' => $totalEstimasiKelebihanBayar,
            'total_sts_disetor' => $totalStsDisetor,
            'last_analyzed' => $auditData['analyzed_at'] ?? now()->translatedFormat('d F Y H:i'),
        ];

        // Resolusi berkas KEL_*.DBF aktif tersentralisasi dari RekonsiliasiSimgajiController
        $totalKeluargaDb = SimgajiKeluarga::count();
        $rekonsiliasiCtrl = app(RekonsiliasiSimgajiController::class);
        $activeKel = $rekonsiliasiCtrl->getActiveDbfFile('kel');

        if (! $activeKel && $totalKeluargaDb > 0) {
            $activeKel = [
                'id' => 'db_synced_kel',
                'type' => 'kel',
                'filename' => 'Basis Data Riwayat Keluarga SIMGAJI (Tersimpan di Database)',
                'stored_name' => 'database',
                'path' => '',
                'size' => 'Tersimpan di DB',
                'records' => $totalKeluargaDb,
                'uploaded_at' => 'Tersinkronisasi di Server',
                'is_active' => true,
                'keterangan' => 'Data tanggungan keluarga aktif dari basis data MySQL',
            ];
        }

        return view('laporan.audit_tunjangan.index', compact(
            'tab',
            'search',
            'skpdFilter',
            'statusFilter',
            'skpdList',
            'paginatedData',
            'stats',
            'activeKel',
            'totalKeluargaDb'
        ));
    }

    /**
     * Store or update case resolution (Penyelesaian Kasus / Catatan Bukti STS).
     */
    public function storeResolusi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => 'required|in:anak,pasangan,kuota',
            'kunci_kasus' => 'required|string|max:255',
            'status' => 'required|in:selesai,pending',
            'no_sts' => 'nullable|string|max:100',
            'tgl_sts' => 'nullable|date',
            'nominal_pengembalian' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]);

        AuditTunjanganResolusi::updateOrCreate(
            ['kunci_kasus' => $validated['kunci_kasus']],
            [
                'kategori' => $validated['kategori'],
                'status' => $validated['status'],
                'no_sts' => $validated['no_sts'],
                'tgl_sts' => $validated['tgl_sts'],
                'nominal_pengembalian' => $validated['nominal_pengembalian'] ?? 0,
                'catatan' => $validated['catatan'],
                'user_id' => Auth::id(),
                'resolved_by_name' => Auth::user()?->name ?? 'Administrator',
            ]
        );

        // Invalidate audit cache so changes reflect instantly
        Cache::forget(self::CACHE_KEY);

        $statusMsg = $validated['status'] === 'selesai'
            ? 'Kasus audit berhasil ditandai SELESAI dengan bukti STS.'
            : 'Status kasus berhasil diperbarui menjadi PENDING (Belum Selesai).';

        return redirect()->back()->with('success', $statusMsg);
    }

    /**
     * Cancel/delete resolution status.
     */
    public function destroyResolusi(int $id): RedirectResponse
    {
        $resolusi = AuditTunjanganResolusi::findOrFail($id);
        $resolusi->delete();

        // Invalidate audit cache
        Cache::forget(self::CACHE_KEY);

        return redirect()->back()->with('success', 'Status penyelesaian kasus telah dibatalkan.');
    }

    /**
     * Clear analysis cache and recalculate immediately.
     */
    public function refreshCache(): RedirectResponse
    {
        Cache::forget(self::CACHE_KEY);

        return redirect()->back()->with('success', 'Analisis audit tunjangan keluarga berhasil disegarkan.');
    }

    /**
     * Compile and cache comprehensive audit datasets from SIMGAJI & Pegawai tables,
     * joined with stored resolution statuses (bukti STS).
     */
    private function getAuditData(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            // Load all settled resolutions keyed by case key
            $resolutions = AuditTunjanganResolusi::where('status', 'selesai')
                ->get()
                ->keyBy('kunci_kasus');

            // -------------------------------------------------------------
            // 1. DOBEL TUNJANGAN ANAK
            // -------------------------------------------------------------
            $rawDoubleChildren = DB::table('simgaji_keluargas as k1')
                ->join('simgaji_keluargas as k2', function ($join) {
                    $join->on('k1.nmkel', '=', 'k2.nmkel')
                        ->on('k1.tgllhr', '=', 'k2.tgllhr')
                        ->whereColumn('k1.nip', '<', 'k2.nip');
                })
                ->leftJoin('pegawais as p1', 'k1.nip', '=', 'p1.nip')
                ->leftJoin('unit_kerjas as u1', 'p1.unit_kerja_id', '=', 'u1.id')
                ->leftJoin('pegawais as p2', 'k2.nip', '=', 'p2.nip')
                ->leftJoin('unit_kerjas as u2', 'p2.unit_kerja_id', '=', 'u2.id')
                ->where('k1.kdtunjang', '2')
                ->where('k2.kdtunjang', '2')
                ->whereIn('k1.kdhubkel', ['11', '12', '13', '14', '15', '21', '22', '23'])
                ->whereIn('k2.kdhubkel', ['11', '12', '13', '14', '15', '21', '22', '23'])
                ->whereRaw('LENGTH(TRIM(k1.nmkel)) > 3')
                ->whereRaw('NOT (k1.nmkel LIKE "ANAK%")')
                ->whereNotIn('k1.nmkel', ['ALUH', '1', '2', '-'])
                ->select([
                    'k1.nmkel as nama_anak',
                    'k1.tgllhr as tgl_lahir_anak',
                    'k1.nip as nip_1',
                    'p1.id as pegawai_id_1',
                    'p1.nama as nama_1',
                    'u1.skpd as skpd_1',
                    'u1.upt as upt_1',
                    'k1.hubungan as hub_1',
                    'k2.nip as nip_2',
                    'p2.id as pegawai_id_2',
                    'p2.nama as nama_2',
                    'u2.skpd as skpd_2',
                    'u2.upt as upt_2',
                    'k2.hubungan as hub_2',
                ])
                ->orderBy('k1.nmkel')
                ->get();

            $dobelAnak = [];
            foreach ($rawDoubleChildren as $item) {
                $nama1 = $item->nama_1;
                if (! $nama1) {
                    $nama1 = DB::table('simgaji_keluargas')->where('nip', $item->nip_1)->where('kdhubkel', '00')->value('nmkel') ?: 'Pegawai SIMGAJI';
                }

                $nama2 = $item->nama_2;
                if (! $nama2) {
                    $nama2 = DB::table('simgaji_keluargas')->where('nip', $item->nip_2)->where('kdhubkel', '00')->value('nmkel') ?: 'Pegawai SIMGAJI';
                }

                $birth = $item->tgl_lahir_anak ? Carbon::parse($item->tgl_lahir_anak) : null;
                $usia = $birth ? $birth->age : null;

                $kunciKasus = 'anak_'.md5(strtoupper(trim($item->nama_anak)).'_'.substr($item->tgl_lahir_anak, 0, 10).'_'.min($item->nip_1, $item->nip_2).'_'.max($item->nip_1, $item->nip_2));
                $res = $resolutions->get($kunciKasus);

                $dobelAnak[] = [
                    'kategori' => 'anak',
                    'kunci_kasus' => $kunciKasus,
                    'is_selesai' => ($res !== null),
                    'resolusi_id' => $res?->id,
                    'no_sts' => $res?->no_sts,
                    'tgl_sts' => $res?->tgl_sts ? Carbon::parse($res->tgl_sts)->format('d/m/Y') : null,
                    'nominal_pengembalian' => (float) ($res?->nominal_pengembalian ?? 0),
                    'catatan' => $res?->catatan,
                    'resolved_by' => $res?->resolved_by_name ?: ($res?->user?->name ?? 'Admin'),
                    'resolved_at' => $res?->updated_at ? Carbon::parse($res->updated_at)->format('d/m/Y H:i') : null,

                    'nama_anak' => trim($item->nama_anak),
                    'tgl_lahir_anak' => $birth ? $birth->format('d/m/Y') : '-',
                    'tgl_lahir_raw' => substr($item->tgl_lahir_anak, 0, 10),
                    'usia_anak' => $usia !== null ? $usia.' tahun' : '-',
                    'nip_1' => $item->nip_1,
                    'pegawai_id_1' => $item->pegawai_id_1,
                    'nama_1' => $nama1,
                    'skpd_1' => $item->skpd_1 ?: 'SKPD SIMGAJI',
                    'upt_1' => $item->upt_1 ?: '-',
                    'hub_1' => $item->hub_1 ?: 'Anak',
                    'nip_2' => $item->nip_2,
                    'pegawai_id_2' => $item->pegawai_id_2,
                    'nama_2' => $nama2,
                    'skpd_2' => $item->skpd_2 ?: 'SKPD SIMGAJI',
                    'upt_2' => $item->upt_2 ?: '-',
                    'hub_2' => $item->hub_2 ?: 'Anak',
                    'potensi_kelebihan_bln' => (int) (self::ESTIMASI_GAPOK_RATA * 0.02),
                ];
            }

            // -------------------------------------------------------------
            // 2. DOBEL PASANGAN SUAMI-ISTRI SALING MENUNJANG (10% + 10%)
            // -------------------------------------------------------------
            $dobelPasangan = [];
            $processedCouples = [];

            // Correlate parents from double children
            foreach ($dobelAnak as $child) {
                $coupleKey = min($child['nip_1'], $child['nip_2']).'_'.max($child['nip_1'], $child['nip_2']);
                if (isset($processedCouples[$coupleKey])) {
                    continue;
                }
                $processedCouples[$coupleKey] = true;

                $sp1 = DB::table('simgaji_keluargas')->where('nip', $child['nip_1'])->whereIn('kdhubkel', ['10', '20'])->first();
                $sp2 = DB::table('simgaji_keluargas')->where('nip', $child['nip_2'])->whereIn('kdhubkel', ['10', '20'])->first();

                if ($sp1 && $sp2 && $sp1->kdtunjang == '2' && $sp2->kdtunjang == '2') {
                    $kunciKasus = 'pasangan_'.$coupleKey;
                    $res = $resolutions->get($kunciKasus);

                    $dobelPasangan[] = [
                        'kategori' => 'pasangan',
                        'kunci_kasus' => $kunciKasus,
                        'is_selesai' => ($res !== null),
                        'resolusi_id' => $res?->id,
                        'no_sts' => $res?->no_sts,
                        'tgl_sts' => $res?->tgl_sts ? Carbon::parse($res->tgl_sts)->format('d/m/Y') : null,
                        'nominal_pengembalian' => (float) ($res?->nominal_pengembalian ?? 0),
                        'catatan' => $res?->catatan,
                        'resolved_by' => $res?->resolved_by_name ?: ($res?->user?->name ?? 'Admin'),
                        'resolved_at' => $res?->updated_at ? Carbon::parse($res->updated_at)->format('d/m/Y H:i') : null,

                        'nip_1' => $child['nip_1'],
                        'pegawai_id_1' => $child['pegawai_id_1'],
                        'nama_1' => $child['nama_1'],
                        'skpd_1' => $child['skpd_1'],
                        'pasangan_di_1' => $sp1->nmkel,
                        'tunjang_1' => 'Tertunjang (10%)',
                        'nip_2' => $child['nip_2'],
                        'pegawai_id_2' => $child['pegawai_id_2'],
                        'nama_2' => $child['nama_2'],
                        'skpd_2' => $child['skpd_2'],
                        'pasangan_di_2' => $sp2->nmkel,
                        'tunjang_2' => 'Tertunjang (10%)',
                        'potensi_kelebihan_bln' => (int) (self::ESTIMASI_GAPOK_RATA * 0.10),
                    ];
                }
            }

            // Also check explicit mutual nipsuamiis in SIMGAJI
            $explicitCouples = DB::table('simgaji_keluargas as k1')
                ->join('simgaji_keluargas as k2', function ($join) {
                    $join->on('k1.nipsuamiis', '=', 'k2.nip')
                        ->whereColumn('k1.nip', '<', 'k2.nip');
                })
                ->leftJoin('pegawais as p1', 'k1.nip', '=', 'p1.nip')
                ->leftJoin('unit_kerjas as u1', 'p1.unit_kerja_id', '=', 'u1.id')
                ->leftJoin('pegawais as p2', 'k2.nip', '=', 'p2.nip')
                ->leftJoin('unit_kerjas as u2', 'p2.unit_kerja_id', '=', 'u2.id')
                ->whereIn('k1.kdhubkel', ['10', '20'])
                ->whereIn('k2.kdhubkel', ['10', '20'])
                ->where('k1.kdtunjang', '2')
                ->where('k2.kdtunjang', '2')
                ->select([
                    'k1.nip as nip_1', 'p1.id as pegawai_id_1', 'p1.nama as nama_1', 'u1.skpd as skpd_1', 'k1.nmkel as pasangan_1',
                    'k2.nip as nip_2', 'p2.id as pegawai_id_2', 'p2.nama as nama_2', 'u2.skpd as skpd_2', 'k2.nmkel as pasangan_2',
                ])
                ->get();

            foreach ($explicitCouples as $ec) {
                $coupleKey = min($ec->nip_1, $ec->nip_2).'_'.max($ec->nip_1, $ec->nip_2);
                if (isset($processedCouples[$coupleKey])) {
                    continue;
                }
                $processedCouples[$coupleKey] = true;

                $nama1 = $ec->nama_1 ?: (DB::table('simgaji_keluargas')->where('nip', $ec->nip_1)->where('kdhubkel', '00')->value('nmkel') ?: 'Pegawai SIMGAJI');
                $nama2 = $ec->nama_2 ?: (DB::table('simgaji_keluargas')->where('nip', $ec->nip_2)->where('kdhubkel', '00')->value('nmkel') ?: 'Pegawai SIMGAJI');

                $kunciKasus = 'pasangan_'.$coupleKey;
                $res = $resolutions->get($kunciKasus);

                $dobelPasangan[] = [
                    'kategori' => 'pasangan',
                    'kunci_kasus' => $kunciKasus,
                    'is_selesai' => ($res !== null),
                    'resolusi_id' => $res?->id,
                    'no_sts' => $res?->no_sts,
                    'tgl_sts' => $res?->tgl_sts ? Carbon::parse($res->tgl_sts)->format('d/m/Y') : null,
                    'nominal_pengembalian' => (float) ($res?->nominal_pengembalian ?? 0),
                    'catatan' => $res?->catatan,
                    'resolved_by' => $res?->resolved_by_name ?: ($res?->user?->name ?? 'Admin'),
                    'resolved_at' => $res?->updated_at ? Carbon::parse($res->updated_at)->format('d/m/Y H:i') : null,

                    'nip_1' => $ec->nip_1,
                    'pegawai_id_1' => $ec->pegawai_id_1,
                    'nama_1' => $nama1,
                    'skpd_1' => $ec->skpd_1 ?: 'SKPD SIMGAJI',
                    'pasangan_di_1' => $ec->pasangan_1,
                    'tunjang_1' => 'Tertunjang (10%)',
                    'nip_2' => $ec->nip_2,
                    'pegawai_id_2' => $ec->pegawai_id_2,
                    'nama_2' => $nama2,
                    'skpd_2' => $ec->skpd_2 ?: 'SKPD SIMGAJI',
                    'pasangan_di_2' => $ec->pasangan_2,
                    'tunjang_2' => 'Tertunjang (10%)',
                    'potensi_kelebihan_bln' => (int) (self::ESTIMASI_GAPOK_RATA * 0.10),
                ];
            }

            // -------------------------------------------------------------
            // 3. PEGAWAI DENGAN JUMLAH ANAK TERTUNJANG > 2 ANAK
            // -------------------------------------------------------------
            $rawOverQuota = DB::table('simgaji_keluargas as k')
                ->join('pegawais as p', 'k.nip', '=', 'p.nip')
                ->leftJoin('unit_kerjas as u', 'p.unit_kerja_id', '=', 'u.id')
                ->where('k.kdtunjang', '2')
                ->whereIn('k.kdhubkel', ['11', '12', '13', '14', '15', '21', '22', '23'])
                ->select([
                    'p.id as pegawai_id',
                    'p.nip',
                    'p.nama',
                    'p.golru as golongan',
                    'u.skpd',
                    'u.upt',
                    DB::raw('COUNT(k.id) as total_anak_tunjang'),
                ])
                ->groupBy('p.id', 'p.nip', 'p.nama', 'p.golru', 'u.skpd', 'u.upt')
                ->havingRaw('COUNT(k.id) > 2')
                ->orderByDesc('total_anak_tunjang')
                ->get();

            $lebihKuota = [];
            foreach ($rawOverQuota as $item) {
                $children = DB::table('simgaji_keluargas')
                    ->where('nip', $item->nip)
                    ->where('kdtunjang', '2')
                    ->whereIn('kdhubkel', ['11', '12', '13', '14', '15', '21', '22', '23'])
                    ->orderBy('kdhubkel')
                    ->get();

                $childDetails = [];
                $childNames = [];
                foreach ($children as $c) {
                    $birth = $c->tgllhr ? Carbon::parse($c->tgllhr) : null;
                    $usia = $birth ? $birth->age.' thn' : '-';
                    $childDetails[] = [
                        'nama' => $c->nmkel,
                        'hubungan' => $c->hubungan ?: 'Anak',
                        'tgl_lahir' => $birth ? $birth->format('d/m/Y') : '-',
                        'usia' => $usia,
                    ];
                    $childNames[] = $c->nmkel.' ('.$usia.')';
                }

                $kelebihan = (int) $item->total_anak_tunjang - 2;
                $kunciKasus = 'kuota_'.$item->nip;
                $res = $resolutions->get($kunciKasus);

                $lebihKuota[] = [
                    'kategori' => 'kuota',
                    'kunci_kasus' => $kunciKasus,
                    'is_selesai' => ($res !== null),
                    'resolusi_id' => $res?->id,
                    'no_sts' => $res?->no_sts,
                    'tgl_sts' => $res?->tgl_sts ? Carbon::parse($res->tgl_sts)->format('d/m/Y') : null,
                    'nominal_pengembalian' => (float) ($res?->nominal_pengembalian ?? 0),
                    'catatan' => $res?->catatan,
                    'resolved_by' => $res?->resolved_by_name ?: ($res?->user?->name ?? 'Admin'),
                    'resolved_at' => $res?->updated_at ? Carbon::parse($res->updated_at)->format('d/m/Y H:i') : null,

                    'pegawai_id' => $item->pegawai_id,
                    'nip' => $item->nip,
                    'nama' => $item->nama,
                    'golongan' => $item->golongan ?: '-',
                    'skpd' => $item->skpd ?: 'SKPD SIMGAJI',
                    'upt' => $item->upt ?: '-',
                    'total_anak_tunjang' => (int) $item->total_anak_tunjang,
                    'kelebihan' => $kelebihan,
                    'daftar_anak' => $childDetails,
                    'daftar_anak_str' => implode(', ', $childNames),
                    'potensi_kelebihan_bln' => (int) ($kelebihan * (self::ESTIMASI_GAPOK_RATA * 0.02)),
                ];
            }

            return [
                'dobel_anak' => $dobelAnak,
                'dobel_pasangan' => $dobelPasangan,
                'lebih_kuota' => $lebihKuota,
                'analyzed_at' => now()->translatedFormat('d F Y H:i'),
            ];
        });
    }

    /**
     * Export Audit Tunjangan Keluarga to Excel workbook.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $auditData = $this->getAuditData();
        $spreadsheet = new Spreadsheet;

        // Sheet 1: Dobel Anak
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Dobel Tunjangan Anak');

        $headers1 = ['No', 'Nama Anak', 'Tgl Lahir', 'Usia', 'Orang Tua 1 (NIP)', 'Orang Tua 1 (Nama)', 'SKPD Orang Tua 1', 'Orang Tua 2 (NIP)', 'Orang Tua 2 (Nama)', 'SKPD Orang Tua 2', 'Status Pelanggaran', 'Status Audit / STS', 'No. Bukti STS', 'Tgl STS', 'Nominal STS (Rp)', 'Catatan Tindak Lanjut'];
        $sheet1->fromArray($headers1, null, 'A1');
        $sheet1->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet1->getStyle('A1:P1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $row = 2;
        foreach ($auditData['dobel_anak'] as $idx => $d) {
            $sheet1->setCellValue('A'.$row, $idx + 1);
            $sheet1->setCellValue('B'.$row, $d['nama_anak']);
            $sheet1->setCellValue('C'.$row, $d['tgl_lahir_anak']);
            $sheet1->setCellValue('D'.$row, $d['usia_anak']);
            $sheet1->setCellValueExplicit('E'.$row, $d['nip_1'], DataType::TYPE_STRING);
            $sheet1->setCellValue('F'.$row, $d['nama_1']);
            $sheet1->setCellValue('G'.$row, $d['skpd_1']);
            $sheet1->setCellValueExplicit('H'.$row, $d['nip_2'], DataType::TYPE_STRING);
            $sheet1->setCellValue('I'.$row, $d['nama_2']);
            $sheet1->setCellValue('J'.$row, $d['skpd_2']);
            $sheet1->setCellValue('K'.$row, 'Tertunjang Ganda (2% + 2%)');
            $sheet1->setCellValue('L'.$row, $d['is_selesai'] ? 'SELESAI (STS)' : 'BELUM SELESAI');
            $sheet1->setCellValue('M'.$row, $d['no_sts'] ?? '-');
            $sheet1->setCellValue('N'.$row, $d['tgl_sts'] ?? '-');
            $sheet1->setCellValue('O'.$row, $d['nominal_pengembalian'] ?? 0);
            $sheet1->setCellValue('P'.$row, $d['catatan'] ?? '-');
            $row++;
        }

        foreach (range('A', 'P') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 2: Dobel Pasangan
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Pasangan Saling Menunjang');

        $headers2 = ['No', 'NIP Suami/P1', 'Nama Suami/P1', 'SKPD Suami/P1', 'Status Tunjangan di P1', 'NIP Istri/P2', 'Nama Istri/P2', 'SKPD Istri/P2', 'Status Tunjangan di P2', 'Status Audit / STS', 'No. Bukti STS', 'Tgl STS', 'Nominal STS (Rp)', 'Catatan Tindak Lanjut'];
        $sheet2->fromArray($headers2, null, 'A1');
        $sheet2->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet2->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $row = 2;
        foreach ($auditData['dobel_pasangan'] as $idx => $p) {
            $sheet2->setCellValue('A'.$row, $idx + 1);
            $sheet2->setCellValueExplicit('B'.$row, $p['nip_1'], DataType::TYPE_STRING);
            $sheet2->setCellValue('C'.$row, $p['nama_1']);
            $sheet2->setCellValue('D'.$row, $p['skpd_1']);
            $sheet2->setCellValue('E'.$row, $p['tunjang_1']);
            $sheet2->setCellValueExplicit('F'.$row, $p['nip_2'], DataType::TYPE_STRING);
            $sheet2->setCellValue('G'.$row, $p['nama_2']);
            $sheet2->setCellValue('H'.$row, $p['skpd_2']);
            $sheet2->setCellValue('I'.$row, $p['tunjang_2']);
            $sheet2->setCellValue('J'.$row, $p['is_selesai'] ? 'SELESAI (STS)' : 'BELUM SELESAI');
            $sheet2->setCellValue('K'.$row, $p['no_sts'] ?? '-');
            $sheet2->setCellValue('L'.$row, $p['tgl_sts'] ?? '-');
            $sheet2->setCellValue('M'.$row, $p['nominal_pengembalian'] ?? 0);
            $sheet2->setCellValue('N'.$row, $p['catatan'] ?? '-');
            $row++;
        }

        foreach (range('A', 'N') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 3: Melebihi Kuota (>2 Anak)
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Melebihi Kuota Anak (>2)');

        $headers3 = ['No', 'NIP', 'Nama Pegawai', 'Golongan', 'SKPD', 'Total Anak Tertunjang', 'Kelebihan Kuota', 'Rincian Nama & Usia Anak', 'Status Audit / STS', 'No. Bukti STS', 'Tgl STS', 'Nominal STS (Rp)', 'Catatan Tindak Lanjut'];
        $sheet3->fromArray($headers3, null, 'A1');
        $sheet3->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet3->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $row = 2;
        foreach ($auditData['lebih_kuota'] as $idx => $k) {
            $sheet3->setCellValue('A'.$row, $idx + 1);
            $sheet3->setCellValueExplicit('B'.$row, $k['nip'], DataType::TYPE_STRING);
            $sheet3->setCellValue('C'.$row, $k['nama']);
            $sheet3->setCellValue('D'.$row, $k['golongan']);
            $sheet3->setCellValue('E'.$row, $k['skpd']);
            $sheet3->setCellValue('F'.$row, $k['total_anak_tunjang']);
            $sheet3->setCellValue('G'.$row, '+'.$k['kelebihan']);
            $sheet3->setCellValue('H'.$row, $k['daftar_anak_str']);
            $sheet3->setCellValue('I'.$row, $k['is_selesai'] ? 'SELESAI (STS)' : 'BELUM SELESAI');
            $sheet3->setCellValue('J'.$row, $k['no_sts'] ?? '-');
            $sheet3->setCellValue('K'.$row, $k['tgl_sts'] ?? '-');
            $sheet3->setCellValue('L'.$row, $k['nominal_pengembalian'] ?? 0);
            $sheet3->setCellValue('M'.$row, $k['catatan'] ?? '-');
            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Audit_Tunjangan_Keluarga_SIMGAJI_'.date('Ymd_His').'.xlsx';
        $tempPath = storage_path('app/'.$fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Export Audit Tunjangan Keluarga to PDF document.
     */
    public function exportPdf(Request $request): Response
    {
        $tab = $request->query('tab', 'anak');
        $auditData = $this->getAuditData();

        $pdf = Pdf::loadView('laporan.audit_tunjangan.pdf', [
            'tab' => $tab,
            'auditData' => $auditData,
            'generatedAt' => now()->translatedFormat('d F Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Audit_Tunjangan_Keluarga_'.$tab.'_'.date('Ymd').'.pdf');
    }
}
