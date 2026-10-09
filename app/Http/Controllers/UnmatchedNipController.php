<?php

namespace App\Http\Controllers;

use App\Models\UnmatchedNip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use XBase\TableReader;

class UnmatchedNipController extends Controller
{
    /**
     * Dapatkan peta status NIP dari database master SIMGAJI (MST_PGW.DBF) dengan caching.
     */
    public function getSimgajiStatusMap(): array
    {
        return Cache::remember('simgaji_nip_status_map_v2', 3600, function () {
            $activeFile = app(RekonsiliasiSimgajiController::class)->getActiveDbfFile('mst_pgw');
            if (! $activeFile || ! file_exists($activeFile['path'])) {
                return [];
            }

            $skpdMap = [
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

            $map = [];
            $today = date('Y-m-d');

            try {
                $table = new TableReader($activeFile['path'], ['encoding' => 'cp850']);
                while ($r = $table->nextRecord()) {
                    $nip = trim((string) $r->get('nip'));
                    if (! $nip) {
                        continue;
                    }

                    $kdstapeg = trim((string) $r->get('kdstapeg'));
                    $tmtstop = trim((string) $r->get('tmtstop'));
                    $kdskpd = trim((string) $r->get('kdskpd'));
                    $nama = trim((string) $r->get('nama'));
                    $catatan = trim((string) $r->get('catatan'));

                    $isStopDate = ($tmtstop && $tmtstop !== '0000-00-00' && $tmtstop <= $today);

                    if ($kdstapeg === '23' || ($isStopDate && in_array($kdstapeg, ['4', '12', '13', '3']))) {
                        $statusKey = 'pensiun';
                        $statusLabel = 'Pensiun (BUP)';
                        $badgeClass = 'badge-pensiun';
                        $diagnosa = 'Pegawai telah purna tugas (BUP). Masuk file TPP kemungkinan pembayaran rapel / susulan hak sebelum pensiun.';
                    } elseif ($kdstapeg === '22') {
                        $statusKey = 'pensiun';
                        $statusLabel = 'Pensiun Sendiri / Stop Sementara';
                        $badgeClass = 'badge-stop';
                        $diagnosa = 'Pegawai pensiun atas permintaan sendiri atau pembayaran gaji disetop sementara.';
                    } elseif ($kdstapeg === '27') {
                        $statusKey = 'meninggal';
                        $statusLabel = 'Meninggal Dunia';
                        $badgeClass = 'badge-meninggal';
                        $diagnosa = 'Pegawai tercatat meninggal dunia di SIMGAJI. Pembayaran TPP kemungkinan merupakan hak terusan / rapel.';
                    } elseif ($kdstapeg === '28') {
                        $statusKey = 'pindah';
                        $statusLabel = 'Pindah Instansi';
                        $badgeClass = 'badge-pindah';
                        $diagnosa = 'Pegawai tercatat mutasi pindah instansi ke luar daerah.';
                    } elseif ($kdstapeg === '24') {
                        $statusKey = 'keluar';
                        $statusLabel = 'Berhenti / Keluar';
                        $badgeClass = 'badge-keluar';
                        $diagnosa = 'Pegawai telah berhenti atau mengundurkan diri.';
                    } elseif ($kdstapeg === '6') {
                        $statusKey = 'cuti';
                        $statusLabel = 'Cuti / CLTN';
                        $badgeClass = 'badge-cuti';
                        $diagnosa = 'Pegawai berstatus Cuti di Luar Tanggungan Negara (CLTN).';
                    } elseif ($kdstapeg === '9') {
                        $statusKey = 'mpp';
                        $statusLabel = 'Masa Persiapan Pensiun (MPP)';
                        $badgeClass = 'badge-mpp';
                        $diagnosa = 'Pegawai dalam Masa Persiapan Pensiun / pemberhentian sementara.';
                    } elseif ($kdstapeg === '1') {
                        $statusKey = 'pejabat';
                        $statusLabel = 'Pejabat Negara';
                        $badgeClass = 'badge-pejabat';
                        $diagnosa = 'Kepala Daerah / Pejabat Negara.';
                    } else {
                        $statusKey = 'aktif_simgaji';
                        $statusLabel = 'Aktif di SIMGAJI';
                        $badgeClass = 'badge-aktif-simgaji';
                        $diagnosa = 'Pegawai aktif di SIMGAJI namun belum terdaftar di Master SIMPEG aplikasi. Perlu sinkronisasi master.';
                    }

                    $skpdNama = $skpdMap[$kdskpd] ?? ($kdskpd ? "SKPD Kode {$kdskpd}" : '-');

                    $map[$nip] = [
                        'nip' => $nip,
                        'nama' => $nama,
                        'kdstapeg' => $kdstapeg,
                        'tmtstop' => $tmtstop,
                        'kdskpd' => $kdskpd,
                        'skpd_nama' => $skpdNama,
                        'catatan' => $catatan,
                        'status_key' => $statusKey,
                        'status_label' => $statusLabel,
                        'badge_class' => $badgeClass,
                        'diagnosa' => $diagnosa,
                    ];
                }
            } catch (\Exception $e) {
                // Ignore failure
            }

            return $map;
        });
    }

    /**
     * Tampilkan halaman daftar log NIP tidak ditemukan saat impor dengan diagnosa cerdas status SIMGAJI.
     */
    public function index(Request $request): View
    {
        $simgajiMap = $this->getSimgajiStatusMap();

        $query = UnmatchedNip::query();

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('jenis_file')) {
            $query->where('jenis_file', $request->jenis_file);
        }

        if ($request->filled('status_pegawai')) {
            $query->where('status_pegawai', $request->status_pegawai);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter Diagnosa Status SIMGAJI
        $statusSimgajiFilter = $request->get('status_simgaji', 'semua');
        if ($statusSimgajiFilter && $statusSimgajiFilter !== 'semua') {
            if ($statusSimgajiFilter === 'tidak_terdaftar') {
                $registeredNips = array_keys($simgajiMap);
                $query->whereNotIn('nip', $registeredNips);
            } else {
                $matchingNips = array_keys(array_filter($simgajiMap, fn ($item) => ($item['status_key'] ?? '') === $statusSimgajiFilter));
                $query->whereIn('nip', $matchingNips);
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Pasangkan informasi SIMGAJI ke setiap baris log
        $logs->getCollection()->transform(function ($log) use ($simgajiMap) {
            $log->simgaji = $simgajiMap[$log->nip] ?? null;

            return $log;
        });

        // Hitung Statistik Diagnosa untuk KPI Cards
        $allUnmatchedNips = UnmatchedNip::pluck('nip')->toArray();
        $countTotal = count($allUnmatchedNips);
        $countPensiun = 0;
        $countMeninggal = 0;
        $countAktifSimgaji = 0;
        $countTidakTerdaftar = 0;
        $countLainnya = 0;

        foreach ($allUnmatchedNips as $nip) {
            if (! isset($simgajiMap[$nip])) {
                $countTidakTerdaftar++;
            } else {
                $st = $simgajiMap[$nip]['status_key'] ?? '';
                if ($st === 'pensiun') {
                    $countPensiun++;
                } elseif ($st === 'meninggal') {
                    $countMeninggal++;
                } elseif ($st === 'aktif_simgaji') {
                    $countAktifSimgaji++;
                } else {
                    $countLainnya++;
                }
            }
        }

        $availablePeriodes = UnmatchedNip::select('periode')->distinct()->whereNotNull('periode')->pluck('periode');
        $statusOptions = ['PNS', 'PPPK', 'PPPK PARUH WAKTU', 'Pejabat Negara'];

        $countPns = UnmatchedNip::where('status_pegawai', 'PNS')->count();
        $countPppk = UnmatchedNip::where('status_pegawai', 'PPPK')->count();

        return view('laporan.unmatched-nip.index', compact(
            'logs',
            'availablePeriodes',
            'statusOptions',
            'statusSimgajiFilter',
            'countTotal',
            'countPensiun',
            'countMeninggal',
            'countAktifSimgaji',
            'countTidakTerdaftar',
            'countLainnya',
            'countPns',
            'countPppk'
        ));
    }

    /**
     * Hapus semua riwayat log NIP yang tidak cocok.
     */
    public function destroyAll(): RedirectResponse
    {
        UnmatchedNip::truncate();

        return redirect()->back()->with('success', 'Semua riwayat Log NIP Tidak Ditemukan berhasil dihapus.');
    }
}
