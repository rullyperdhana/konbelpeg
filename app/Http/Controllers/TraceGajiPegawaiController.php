<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TraceGajiPegawaiController extends Controller
{
    /**
     * Map nama bulan bahasa Indonesia ke angka urutan.
     *
     * @var array<string, int>
     */
    private const MONTH_MAP = [
        'januari' => 1,
        'februari' => 2,
        'maret' => 3,
        'april' => 4,
        'mei' => 5,
        'juni' => 6,
        'juli' => 7,
        'agustus' => 8,
        'september' => 9,
        'oktober' => 10,
        'november' => 11,
        'desember' => 12,
    ];

    /**
     * Tampilkan halaman Trace Daftar Penggajian Pegawai Per Orang.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $skpdFilter = $request->input('skpd');
        $statusFilter = $request->input('status_pegawai');
        $selectedPegawaiId = $request->input('pegawai_id');

        // Master pilihan filter
        $skpdOptions = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();
        $statusOptions = ['PNS', 'PPPK', 'PPPK PARUH WAKTU'];
        $availablePeriodes = RealisasiGaji::select('periode')->distinct()->pluck('periode');

        // Statistik global untuk banner / overview
        $globalStats = [
            'total_pegawai' => Pegawai::count(),
            'total_gaji_records' => RealisasiGaji::count(),
            'total_periode' => $availablePeriodes->count(),
        ];

        $selectedPegawai = null;
        $pegawaiList = null;
        $samplePegawais = collect();

        // 1. Jika ada pegawai_id spesifik yang dipilih langsung
        if ($selectedPegawaiId) {
            $selectedPegawai = Pegawai::with(['unitKerja', 'jabatan', 'realisasiTpps', 'simgajiKeluargas'])->find($selectedPegawaiId);
        }

        // 2. Jika ada query pencarian (NIP atau Nama)
        if ($search !== '' || $skpdFilter || $statusFilter) {
            // Cek pencarian NIP presisi (jika user memasukkan NIP lengkap)
            if ($search !== '' && ! $selectedPegawai) {
                $exactMatch = Pegawai::where('nip', $search)->with(['unitKerja', 'jabatan', 'realisasiTpps', 'simgajiKeluargas'])->first();
                if ($exactMatch) {
                    $selectedPegawai = $exactMatch;
                }
            }

            $query = Pegawai::with(['unitKerja', 'jabatan'])->withCount('realisasiGajis');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            }

            if ($skpdFilter) {
                $query->whereHas('unitKerja', function ($q) use ($skpdFilter) {
                    $q->where('skpd', $skpdFilter);
                });
            }

            if ($statusFilter) {
                $query->where('status_pegawai', $statusFilter);
            }

            $pegawaiList = $query->orderBy('nama')->paginate(12)->withQueryString();

            // Jika hasil pencarian hanya 1 pegawai dan belum ada yang dipilih, otomatis pilih pegawai tersebut
            if ($pegawaiList->total() === 1 && ! $selectedPegawai) {
                $selectedPegawai = Pegawai::with(['unitKerja', 'jabatan', 'realisasiTpps', 'simgajiKeluargas'])->find($pegawaiList->first()->id);
            }
        } elseif (! $selectedPegawai) {
            // Jika belum ada pencarian, tampilkan daftar pegawai contoh yang memiliki riwayat gaji
            $recentPegawaiIds = RealisasiGaji::latest('id')->limit(24)->pluck('pegawai_id')->unique()->take(8);
            $samplePegawais = Pegawai::with(['unitKerja', 'jabatan'])
                ->withCount('realisasiGajis')
                ->whereIn('id', $recentPegawaiIds)
                ->get();
        }

        // 3. Jika seorang pegawai sedang dipilih, susun rincian trace penggajian
        $traceData = null;
        if ($selectedPegawai) {
            $traceData = $this->prepareTraceData($selectedPegawai);
        }

        return view('laporan.trace-gaji.index', [
            'search' => $search,
            'skpdFilter' => $skpdFilter,
            'statusFilter' => $statusFilter,
            'skpdOptions' => $skpdOptions,
            'statusOptions' => $statusOptions,
            'availablePeriodes' => $availablePeriodes,
            'globalStats' => $globalStats,
            'selectedPegawai' => $selectedPegawai,
            'pegawaiList' => $pegawaiList,
            'samplePegawais' => $samplePegawais,
            'traceData' => $traceData,
        ]);
    }

    /**
     * Tampilkan lembar trace penggajian pegawai berdasarkan ID pegawai (Direct Route).
     */
    public function show(Pegawai $pegawai, Request $request): View
    {
        $request->merge(['pegawai_id' => $pegawai->id]);

        return $this->index($request);
    }

    /**
     * Ekspor PDF Rekapitulasi Trace Riwayat Penggajian Pegawai.
     */
    public function exportPdf(Pegawai $pegawai): Response
    {
        $pegawai->load(['unitKerja', 'jabatan', 'realisasiTpps']);
        $traceData = $this->prepareTraceData($pegawai);

        $pdf = Pdf::loadView('laporan.trace-gaji.pdf-rekap', [
            'pegawai' => $pegawai,
            'traceData' => $traceData,
            'tanggalCetak' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pegawai->nama);

        return $pdf->download("Trace_Penggajian_{$pegawai->nip}_{$cleanName}.pdf");
    }

    /**
     * Cetak PDF Slip Rincian Gaji Resmi per Bulan untuk Pegawai Terpilih.
     */
    public function slipPdf(Pegawai $pegawai, RealisasiGaji $gaji): Response
    {
        $pegawai->load(['unitKerja', 'jabatan']);
        $item = $this->formatGajiRecord($gaji);

        $pdf = Pdf::loadView('laporan.trace-gaji.pdf-slip', [
            'pegawai' => $pegawai,
            'gaji' => $gaji,
            'item' => $item,
            'terbilang' => $this->terbilang((int) round($item['gaji_bersih'])).' Rupiah',
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'portrait');

        $cleanPeriode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $gaji->periode ?? 'Bulan');

        return $pdf->download("Slip_Gaji_{$pegawai->nip}_{$cleanPeriode}.pdf");
    }

    /**
     * Mempersiapkan dan menghitung seluruh data trace penggajian pegawai secara mendalam.
     *
     * @return array<string, mixed>
     */
    private function prepareTraceData(Pegawai $pegawai): array
    {
        $rawRecords = RealisasiGaji::where('pegawai_id', $pegawai->id)->get();

        // Urutkan riwayat secara kronologis (terbaru di atas)
        $sortedRecords = $rawRecords->sort(function (RealisasiGaji $a, RealisasiGaji $b) {
            $keyA = $this->getPeriodSortKey((string) $a->periode);
            $keyB = $this->getPeriodSortKey((string) $b->periode);

            return $keyB <=> $keyA;
        });

        $formattedList = [];
        $totals = [
            'gaji_pokok' => 0.0,
            'tj_istri' => 0.0,
            'tj_anak' => 0.0,
            'tj_keluarga' => 0.0,
            'tj_jabatan' => 0.0,
            'tj_fungsional' => 0.0,
            'tj_umum' => 0.0,
            'tj_beras' => 0.0,
            'tj_pajak' => 0.0,
            'tj_pembulatan' => 0.0,
            'tj_lain' => 0.0,
            'gaji_kotor' => 0.0,
            'pot_pajak' => 0.0,
            'pot_iwp2' => 0.0,
            'pot_iwp8' => 0.0,
            'pot_iwp_total' => 0.0,
            'pot_taperum' => 0.0,
            'pot_lain' => 0.0,
            'total_potongan' => 0.0,
            'gaji_bersih' => 0.0,
        ];

        $latestRawMeta = [];

        foreach ($sortedRecords as $gaji) {
            $f = $this->formatGajiRecord($gaji);
            $formattedList[] = $f;

            if (empty($latestRawMeta) && ! empty($f['raw'])) {
                $latestRawMeta = $f['raw'];
            }

            $totals['gaji_pokok'] += $f['gaji_pokok'];
            $totals['tj_istri'] += $f['tj_istri'];
            $totals['tj_anak'] += $f['tj_anak'];
            $totals['tj_keluarga'] += $f['tj_keluarga'];
            $totals['tj_jabatan'] += $f['tj_jabatan'];
            $totals['tj_fungsional'] += $f['tj_fungsional'];
            $totals['tj_umum'] += $f['tj_umum'];
            $totals['tj_beras'] += $f['tj_beras'];
            $totals['tj_pajak'] += $f['tj_pajak'];
            $totals['tj_pembulatan'] += $f['tj_pembulatan'];
            $totals['tj_lain'] += $f['tj_lain'];
            $totals['gaji_kotor'] += $f['gaji_kotor'];
            $totals['pot_pajak'] += $f['pot_pajak'];
            $totals['pot_iwp2'] += $f['pot_iwp2'];
            $totals['pot_iwp8'] += $f['pot_iwp8'];
            $totals['pot_iwp_total'] += $f['pot_iwp_total'];
            $totals['pot_taperum'] += $f['pot_taperum'];
            $totals['pot_lain'] += $f['pot_lain'];
            $totals['total_potongan'] += $f['total_potongan'];
            $totals['gaji_bersih'] += $f['gaji_bersih'];
        }

        $periodeCount = count($formattedList);
        $rataRataBersih = $periodeCount > 0 ? ($totals['gaji_bersih'] / $periodeCount) : 0.0;

        // Trace Realisasi TPP jika tersedia
        $tppRecords = $pegawai->realisasiTpps ?? collect();
        $totalTppBersih = $tppRecords->sum('total_dibayarkan');

        return [
            'records' => $formattedList,
            'totals' => $totals,
            'periodeCount' => $periodeCount,
            'rataRataBersih' => $rataRataBersih,
            'latestRawMeta' => $latestRawMeta,
            'tppRecords' => $tppRecords,
            'totalTppBersih' => $totalTppBersih,
            'totalAkumulasiPenghasilan' => $totals['gaji_bersih'] + $totalTppBersih,
            'keluargas' => $pegawai->simgajiKeluargas ?? collect(),
        ];
    }

    /**
     * Ekstrak dan format komponen gaji per record dari data database & raw_data SIMGAJI.
     *
     * @return array<string, mixed>
     */
    private function formatGajiRecord(RealisasiGaji $gaji): array
    {
        $raw = is_array($gaji->raw_data) ? $gaji->raw_data : [];

        $gajiPokok = (float) ($raw['gapok'] ?? $gaji->gaji_pokok ?? 0);
        $tjIstri = (float) ($raw['tjistri'] ?? 0);
        $tjAnak = (float) ($raw['tjanak'] ?? 0);
        $tjKeluarga = $tjIstri + $tjAnak;

        $tjStruk = (float) ($raw['tjstruk'] ?? 0);
        $tjEselon = (float) ($raw['tjeselon'] ?? 0);
        $tjJabatan = $tjStruk + $tjEselon;
        $tjFungsi = (float) ($raw['tjfungsi'] ?? 0);
        $tjUmum = (float) ($raw['tjumum'] ?? 0);
        $tjBeras = (float) ($raw['tjberas'] ?? 0);
        $tjGuru = (float) ($raw['tjguru'] ?? 0);
        $tjPajak = (float) ($raw['tjpajak'] ?? $raw['ppajak'] ?? $gaji->pajak ?? 0);
        $tjPembulatan = (float) ($raw['tbulat'] ?? 0);

        $tjLain = (float) ($raw['tjkhusus'] ?? 0)
            + (float) ($raw['tjlangka'] ?? 0)
            + (float) ($raw['tjterpenci'] ?? 0)
            + (float) ($raw['tjtkd'] ?? 0)
            + (float) ($raw['tjtpp'] ?? 0)
            + (float) ($raw['tjaskes'] ?? 0)
            + (float) ($raw['ttaperapk'] ?? 0)
            + $tjGuru;

        $gajiKotor = isset($raw['kotor']) && (float) $raw['kotor'] > 0
            ? (float) $raw['kotor']
            : ($gajiPokok + $tjKeluarga + $tjJabatan + $tjFungsi + $tjUmum + $tjBeras + $tjPajak + $tjPembulatan + $tjLain);

        // Potongan-potongan
        $potPajak = (float) ($raw['ppajak'] ?? $gaji->pajak ?? 0);
        $potIwp2 = (float) ($raw['piwp2'] ?? 0);
        $potIwp8 = (float) ($raw['piwp8'] ?? 0);
        $potIwpTotal = isset($raw['piwp']) && (float) $raw['piwp'] > 0
            ? (float) $raw['piwp']
            : ($potIwp2 + $potIwp8 ?: (float) ($gaji->iwp ?? 0));

        $potTaperum = (float) ($raw['ptaperum'] ?? 0);
        $potAskes = (float) ($raw['paskes'] ?? 0);
        $potKorpri = (float) ($raw['pkorpri'] ?? 0);
        $potKoperasi = (float) ($raw['pkoperasi'] ?? 0);
        $potSewa = (float) ($raw['psewa'] ?? 0);
        $potBulog = (float) ($raw['pbulog'] ?? 0);
        $potHutang = (float) ($raw['phutang'] ?? 0);
        $potLainFallback = (float) ($gaji->potongan_lain ?? 0);

        $potLain = $potTaperum + $potAskes + $potKorpri + $potKoperasi + $potSewa + $potBulog + $potHutang + $potLainFallback;

        $totalPotongan = isset($raw['potongan']) && (float) $raw['potongan'] > 0
            ? (float) $raw['potongan']
            : ($potPajak + $potIwpTotal + $potLainFallback);

        $gajiBersih = isset($raw['bersih']) && (float) $raw['bersih'] > 0
            ? (float) $raw['bersih']
            : (float) ($gaji->gaji_bersih ?? ($gajiKotor - $totalPotongan));

        return [
            'id' => $gaji->id,
            'periode' => $gaji->periode,
            'jenis_gaji' => $gaji->jenis_gaji ?? 'Gaji Induk',
            'sub_kegiatan' => $gaji->sub_kegiatan,
            'gaji_pokok' => $gajiPokok,
            'tj_istri' => $tjIstri,
            'tj_anak' => $tjAnak,
            'tj_keluarga' => $tjKeluarga,
            'tj_jabatan' => $tjJabatan,
            'tj_fungsional' => $tjFungsi,
            'tj_umum' => $tjUmum,
            'tj_beras' => $tjBeras,
            'tj_guru' => $tjGuru,
            'tj_pajak' => $tjPajak,
            'tj_pembulatan' => $tjPembulatan,
            'tj_lain' => $tjLain,
            'gaji_kotor' => $gajiKotor,
            'pot_pajak' => $potPajak,
            'pot_iwp2' => $potIwp2,
            'pot_iwp8' => $potIwp8,
            'pot_iwp_total' => $potIwpTotal,
            'pot_taperum' => $potTaperum,
            'pot_askes' => $potAskes,
            'pot_korpri' => $potKorpri,
            'pot_koperasi' => $potKoperasi,
            'pot_sewa' => $potSewa,
            'pot_bulog' => $potBulog,
            'pot_hutang' => $potHutang,
            'pot_lain' => $potLain,
            'total_potongan' => $totalPotongan,
            'gaji_bersih' => $gajiBersih,
            'raw' => $raw,
        ];
    }

    /**
     * Hitung nilai sortable integer dari string periode (misal: "Oktober 2026" => 202610).
     */
    private function getPeriodSortKey(string $periode): int
    {
        $parts = preg_split('/\s+/', strtolower(trim($periode)));
        if (count($parts) >= 2) {
            $bulanName = $parts[0];
            $tahun = (int) $parts[1];
            $bulanNum = self::MONTH_MAP[$bulanName] ?? 1;

            return ($tahun * 100) + $bulanNum;
        }

        return 0;
    }

    /**
     * Konversi bilangan numerik menjadi kalimat terbilang bahasa Indonesia.
     */
    private function terbilang(int $angka): string
    {
        $angka = abs($angka);
        $baca = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas',
        ];

        if ($angka < 12) {
            return $baca[$angka];
        }

        if ($angka < 20) {
            return $this->terbilang($angka - 10).' Belas';
        }

        if ($angka < 100) {
            return $this->terbilang((int) ($angka / 10)).' Puluh '.$this->terbilang($angka % 10);
        }

        if ($angka < 200) {
            return 'Seratus '.$this->terbilang($angka - 100);
        }

        if ($angka < 1000) {
            return $this->terbilang((int) ($angka / 100)).' Ratus '.$this->terbilang($angka % 100);
        }

        if ($angka < 2000) {
            return 'Seribu '.$this->terbilang($angka - 1000);
        }

        if ($angka < 1000000) {
            return $this->terbilang((int) ($angka / 1000)).' Ribu '.$this->terbilang($angka % 1000);
        }

        if ($angka < 1000000000) {
            return $this->terbilang((int) ($angka / 1000000)).' Juta '.$this->terbilang($angka % 1000000);
        }

        if ($angka < 1000000000000) {
            return $this->terbilang((int) ($angka / 1000000000)).' Miliar '.$this->terbilang($angka % 1000000000);
        }

        return (string) $angka;
    }
}
