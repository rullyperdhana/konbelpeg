<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use XBase\TableReader;

class RekonsiliasiSimgajiController extends Controller
{
    private array $mapPangkat = [
        '1A' => 'I/a', '1B' => 'I/b', '1C' => 'I/c', '1D' => 'I/d',
        '2A' => 'II/a', '2B' => 'II/b', '2C' => 'II/c', '2D' => 'II/d',
        '3A' => 'III/a', '3B' => 'III/b', '3C' => 'III/c', '3D' => 'III/d',
        '4A' => 'IV/a', '4B' => 'IV/b', '4C' => 'IV/c', '4D' => 'IV/d', '4E' => 'IV/e',
        // PPPK: Konversi angka normal SIMGAJI (1-17 / 01-17) ke angka Romawi (I-XVII)
        '01' => 'I', '1' => 'I',
        '02' => 'II', '2' => 'II',
        '03' => 'III', '3' => 'III',
        '04' => 'IV', '4' => 'IV',
        '05' => 'V', '5' => 'V',
        '06' => 'VI', '6' => 'VI',
        '07' => 'VII', '7' => 'VII',
        '08' => 'VIII', '8' => 'VIII',
        '09' => 'IX', '9' => 'IX',
        '10' => 'X',
        '11' => 'XI',
        '12' => 'XII',
        '13' => 'XIII',
        '14' => 'XIV',
        '15' => 'XV',
        '16' => 'XVI',
        '17' => 'XVII',
    ];

    private function isSamePangkat(?string $appPangkat, ?string $simgajiPangkat, ?string $rawDbf = null): bool
    {
        if (! $appPangkat || ! $simgajiPangkat) {
            return false;
        }

        $cleanApp = strtoupper(trim(str_replace(' ', '', $appPangkat)));
        $cleanSimgaji = strtoupper(trim(str_replace(' ', '', $simgajiPangkat)));
        $cleanRaw = strtoupper(trim(str_replace(' ', '', (string) $rawDbf)));

        if ($cleanApp === $cleanSimgaji) {
            return true;
        }

        // Peta normalisasi Angka Romawi PPPK <=> Angka Normal Arab (1 - 17)
        $romawiMap = [
            '1' => 'I', '01' => 'I', 'I' => 'I',
            '2' => 'II', '02' => 'II', 'II' => 'II',
            '3' => 'III', '03' => 'III', 'III' => 'III',
            '4' => 'IV', '04' => 'IV', 'IV' => 'IV',
            '5' => 'V', '05' => 'V', 'V' => 'V',
            '6' => 'VI', '06' => 'VI', 'VI' => 'VI',
            '7' => 'VII', '07' => 'VII', 'VII' => 'VII',
            '8' => 'VIII', '08' => 'VIII', 'VIII' => 'VIII',
            '9' => 'IX', '09' => 'IX', 'IX' => 'IX',
            '10' => 'X', 'X' => 'X',
            '11' => 'XI', 'XI' => 'XI',
            '12' => 'XII', 'XII' => 'XII',
            '13' => 'XIII', 'XIII' => 'XIII',
            '14' => 'XIV', 'XIV' => 'XIV',
            '15' => 'XV', 'XV' => 'XV',
            '16' => 'XVI', 'XVI' => 'XVI',
            '17' => 'XVII', 'XVII' => 'XVII',
        ];

        $appNorm = $romawiMap[$cleanApp] ?? $cleanApp;
        $simgajiNorm = $romawiMap[$cleanSimgaji] ?? ($romawiMap[$cleanRaw] ?? $cleanSimgaji);

        return $appNorm === $simgajiNorm;
    }

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

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'aktif_baru');
        $search = trim((string) $request->get('search', ''));
        $statusPensiun = $request->get('status_pensiun', 'semua');
        $statusSk = $request->get('status_sk', 'semua');

        $data = $this->getReconciliationData();

        if (isset($data['error'])) {
            return view('laporan.rekonsiliasi_simgaji.index', [
                'error' => $data['error'],
                'summary' => [],
                'paginatedItems' => new LengthAwarePaginator([], 0, 25),
                'activeTab' => $activeTab,
                'search' => $search,
                'statusPensiun' => $statusPensiun,
                'statusSk' => $statusSk,
            ]);
        }

        $items = collect($data[$activeTab] ?? []);

        // Filter untuk beda_pangkat (status pensiun & status SK di SIMGAJI)
        if ($activeTab === 'beda_pangkat') {
            if ($statusPensiun === 'aktif') {
                $items = $items->filter(fn ($item) => empty($item['is_pensiun']));
            } elseif ($statusPensiun === 'pensiun') {
                $items = $items->filter(fn ($item) => ! empty($item['is_pensiun']));
            }

            if ($statusSk === 'belum_diinput') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'belum_diinput');
            } elseif ($statusSk === 'sudah_terjadwal') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'sudah_terjadwal');
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_tpp'] ?? ''), $searchLower);
            });
        }

        // Paginate collection
        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedItems = new LengthAwarePaginator($currentPageItems, $items->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $activeDbfMeta = Cache::get('rekonsiliasi_active_dbf');
        if (! $activeDbfMeta) {
            $rootCandidates = glob(base_path('MST_PGW*.DBF')) ?: (glob(base_path('mst_pgw*.dbf')) ?: glob(base_path('*.DBF')));
            $defaultFile = ! empty($rootCandidates) ? $rootCandidates[0] : base_path('MST_PGW_2026-9-011600.DBF');
            $activeDbfMeta = [
                'filename' => file_exists($defaultFile) ? basename($defaultFile) : 'Belum Ada',
                'uploaded_at' => file_exists($defaultFile) ? date('d M Y H:i', filemtime($defaultFile)) : '-',
                'filesize' => file_exists($defaultFile) ? round(filesize($defaultFile) / (1024 * 1024), 2).' MB' : '0 MB',
            ];
        }

        return view('laporan.rekonsiliasi_simgaji.index', [
            'summary' => $data['summary'],
            'paginatedItems' => $paginatedItems,
            'activeTab' => $activeTab,
            'search' => $search,
            'statusPensiun' => $statusPensiun,
            'statusSk' => $statusSk,
            'activeDbfMeta' => $activeDbfMeta,
        ]);
    }

    public function syncPegawai(Request $request)
    {
        $data = $this->getReconciliationData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $aktifBaru = collect($data['aktif_baru'])->keyBy('nip');
        $selectedNips = $request->input('selected_nips', []);
        $syncAll = $request->boolean('sync_all');

        $targetNips = $syncAll ? $aktifBaru->keys()->toArray() : (array) $selectedNips;

        if (empty($targetNips)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pegawai untuk disinkronkan.');
        }

        $defaultUnitKerja = UnitKerja::first();
        $unitKerjaId = $defaultUnitKerja ? $defaultUnitKerja->id : 1;
        $successCount = 0;

        DB::beginTransaction();
        try {
            foreach ($targetNips as $nip) {
                if (! isset($aktifBaru[$nip])) {
                    continue;
                }
                $row = $aktifBaru[$nip];

                if (Pegawai::where('nip', $nip)->exists()) {
                    continue;
                }

                $statusPegawai = in_array($row['kdstapeg'], ['4', '23', '24', '1', '2', '3']) ? 'PNS' : 'PPPK';

                // Cari unit kerja yang cocok dari kode SKPD
                $targetSkpd = $this->skpdCodeMap[$row['kdskpd']] ?? null;
                $matchedUnit = $targetSkpd ? UnitKerja::where('skpd', $targetSkpd)->first() : null;
                $assignedUnitKerjaId = $matchedUnit ? $matchedUnit->id : $unitKerjaId;

                Pegawai::create([
                    'nip' => $nip,
                    'nama' => $row['nama'],
                    'tempat_lahir' => $row['tempatlhr'] ?: '-',
                    'tgl_lahir' => ! empty($row['tgllhr']) ? date('Y-m-d', strtotime($row['tgllhr'])) : '1980-01-01',
                    'jk' => ($row['kdjenkel'] ?? '1') == '2' ? 'PEREMPUAN' : 'LAKI-LAKI',
                    'agama' => 'ISLAM',
                    'status_pegawai' => $statusPegawai,
                    'golru' => $row['golru_converted'] ?: '-',
                    'tmt_golru' => date('d-m-Y'),
                    'masa_kerja_tahun' => (int) ($row['masker'] ?? 0),
                    'masa_kerja_bulan' => 0,
                    'tk_ijazah' => $row['pendidikan'] ?: '-',
                    'nm_pendidikan' => '-',
                    'th_lulus' => date('Y'),
                    'jabatan_id' => 1,
                    'unit_kerja_id' => $assignedUnitKerjaId,
                    'jenis_pegawai' => 'TEKNIS',
                ]);

                $successCount++;
            }

            DB::commit();
            $this->getReconciliationData(true);

            return redirect()->back()->with('success', "Berhasil menyinkronkan {$successCount} data pegawai baru ke Master Pegawai.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat sinkronisasi: '.$e->getMessage());
        }
    }

    public function syncPangkat(Request $request)
    {
        $data = $this->getReconciliationData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $bedaPangkat = collect($data['beda_pangkat'])->keyBy('nip');
        $selectedNips = $request->input('selected_nips', []);
        $syncAll = $request->boolean('sync_all');

        $targetNips = $syncAll ? $bedaPangkat->keys()->toArray() : (array) $selectedNips;

        if (empty($targetNips)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pegawai untuk diperbarui pangkatnya.');
        }

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($targetNips as $nip) {
                if (! isset($bedaPangkat[$nip])) {
                    continue;
                }
                $row = $bedaPangkat[$nip];

                Pegawai::where('nip', $nip)->update([
                    'golru' => $row['pangkat_simgaji'],
                ]);
                $updatedCount++;
            }

            DB::commit();
            $this->getReconciliationData(true);

            return redirect()->back()->with('success', "Berhasil memperbarui pangkat/golru {$updatedCount} pegawai sesuai data SIMGAJI.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat pembaruan pangkat: '.$e->getMessage());
        }
    }

    public function syncSkpd(Request $request)
    {
        $data = $this->getReconciliationData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $bedaSkpd = collect($data['beda_skpd'])->keyBy('nip');
        $selectedNips = $request->input('selected_nips', []);
        $syncAll = $request->boolean('sync_all');

        $targetNips = $syncAll ? $bedaSkpd->keys()->toArray() : (array) $selectedNips;

        if (empty($targetNips)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pegawai untuk disinkronkan SKPD-nya.');
        }

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($targetNips as $nip) {
                if (! isset($bedaSkpd[$nip])) {
                    continue;
                }
                $row = $bedaSkpd[$nip];

                $targetSkpd = $row['skpd_simgaji'];
                $unitKerja = UnitKerja::where('skpd', $targetSkpd)->first();
                if ($unitKerja) {
                    Pegawai::where('nip', $nip)->update([
                        'unit_kerja_id' => $unitKerja->id,
                    ]);
                    $updatedCount++;
                }
            }

            DB::commit();
            $this->getReconciliationData(true);

            return redirect()->back()->with('success', "Berhasil memperbarui unit kerja/SKPD {$updatedCount} pegawai sesuai data penempatan SIMGAJI.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat pembaruan SKPD: '.$e->getMessage());
        }
    }

    public function syncJabatan(Request $request)
    {
        $data = $this->getReconciliationData();
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $bedaJabatan = collect($data['beda_jabatan'])->keyBy('nip');
        $selectedNips = $request->input('selected_nips', []);
        $syncAll = $request->boolean('sync_all');

        $targetNips = $syncAll ? $bedaJabatan->keys()->toArray() : (array) $selectedNips;

        if (empty($targetNips)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pegawai untuk disinkronkan jabatannya.');
        }

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($targetNips as $nip) {
                if (! isset($bedaJabatan[$nip])) {
                    continue;
                }
                $row = $bedaJabatan[$nip];

                $namaJabatan = trim($row['jabatan_tpp']);
                if (! $namaJabatan || $namaJabatan === '-') {
                    continue;
                }

                // Cari atau buat jabatan di tabel jabatans
                $jabatan = Jabatan::firstOrCreate(
                    ['nama' => $namaJabatan],
                    ['eselon' => $row['eselon_dbf'] ?? '-', 'jenis' => 'FUNGSIONAL']
                );

                Pegawai::where('nip', $nip)->update([
                    'jabatan_id' => $jabatan->id,
                ]);
                $updatedCount++;
            }

            DB::commit();
            $this->getReconciliationData(true);

            return redirect()->back()->with('success', "Berhasil memperbarui jabatan {$updatedCount} pegawai sesuai data mutakhir penggajian.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat pembaruan jabatan: '.$e->getMessage());
        }
    }

    public function uploadPage()
    {
        $files = $this->getDbfManifest();
        $activeMstPgw = $this->getActiveDbfFile('mst_pgw');
        $activeHisGpok = $this->getActiveDbfFile('his_gpok');

        return view('master.simgaji_dbf', [
            'files' => $files,
            'activeMstPgw' => $activeMstPgw,
            'activeHisGpok' => $activeHisGpok,
            'activeFile' => $activeMstPgw,
        ]);
    }

    public function uploadDbf(Request $request)
    {
        $request->validate([
            'file_dbf' => 'required|file',
            'jenis_dbf' => 'nullable|string|in:auto,mst_pgw,his_gpok',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file_dbf');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'dbf') {
            return redirect()->back()->with('error', 'File yang diunggah harus berformat database SIMGAJI (.dbf atau .DBF).');
        }

        $originalName = $file->getClientOriginalName();
        $targetDir = storage_path('app/simgaji');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
        $storedName = time().'_'.$cleanFilename;
        $file->move($targetDir, $storedName);
        $fullPath = $targetDir.'/'.$storedName;

        // Deteksi jenis file dan hitung total record
        $recordsCount = 0;
        $detectedType = 'mst_pgw';
        try {
            $table = new TableReader($fullPath, ['encoding' => 'cp850']);
            $recordsCount = $table->getRecordCount();

            $colNames = [];
            foreach ($table->getColumns() as $col) {
                $colNames[] = strtolower($col->getName());
            }

            if (in_array('nomorskep', $colNames) || in_array('penerbitsk', $colNames) || in_array('tmtgaji', $colNames) || str_contains(strtoupper($originalName), 'HIS_GPOK')) {
                $detectedType = 'his_gpok';
            }
        } catch (\Exception $e) {
            if (str_contains(strtoupper($originalName), 'HIS_GPOK')) {
                $detectedType = 'his_gpok';
            }
        }

        $reqType = $request->input('jenis_dbf', 'auto');
        $finalType = in_array($reqType, ['mst_pgw', 'his_gpok']) ? $reqType : $detectedType;

        $files = $this->getDbfManifest();

        // Nonaktifkan file lain yang memiliki tipe yang sama
        foreach ($files as &$f) {
            $fType = $f['type'] ?? 'mst_pgw';
            if ($fType === $finalType) {
                $f['is_active'] = false;
            }
        }

        $typeLabel = $finalType === 'his_gpok' ? 'Histori Gaji Pokok & SK (HIS_GPOK)' : 'Master Pegawai (MST_PGW)';
        $newId = uniqid('dbf_');
        $files[] = [
            'id' => $newId,
            'type' => $finalType,
            'filename' => $originalName,
            'stored_name' => $storedName,
            'path' => $fullPath,
            'size' => round(filesize($fullPath) / (1024 * 1024), 2).' MB',
            'records' => $recordsCount,
            'keterangan' => $request->input('keterangan') ?: 'Unggahan '.date('d M Y H:i'),
            'uploaded_at' => date('d M Y H:i'),
            'is_active' => true,
        ];

        $this->saveDbfManifest($files);
        Cache::forget('rekonsiliasi_simgaji_data');

        return redirect()->back()->with('success', "File database {$typeLabel} '{$originalName}' (".number_format($recordsCount, 0, ',', '.').' record) berhasil diunggah dan dijadikan acuan aktif!');
    }

    public function setActiveDbf($id)
    {
        $files = $this->getDbfManifest();
        $targetType = null;
        foreach ($files as $f) {
            if ($f['id'] === $id) {
                $targetType = $f['type'] ?? 'mst_pgw';
                break;
            }
        }

        if (! $targetType) {
            return redirect()->back()->with('error', 'File database tidak ditemukan.');
        }

        $activated = false;
        $name = '';

        foreach ($files as &$f) {
            $fType = $f['type'] ?? 'mst_pgw';
            if ($fType === $targetType) {
                if ($f['id'] === $id) {
                    $f['is_active'] = true;
                    $activated = true;
                    $name = $f['filename'];
                } else {
                    $f['is_active'] = false;
                }
            }
        }

        if ($activated) {
            $this->saveDbfManifest($files);
            Cache::forget('rekonsiliasi_simgaji_data');

            $label = $targetType === 'his_gpok' ? 'Histori Gaji Pokok & SK' : 'Master Pegawai';

            return redirect()->back()->with('success', "Database acuan aktif untuk {$label} berhasil dialihkan ke '{$name}'.");
        }

        return redirect()->back()->with('error', 'File database tidak ditemukan.');
    }

    public function deleteDbf($id)
    {
        $files = $this->getDbfManifest();
        $newFiles = [];
        $wasActive = false;
        $deletedName = '';
        $deletedType = 'mst_pgw';

        foreach ($files as $f) {
            if ($f['id'] === $id) {
                $wasActive = ! empty($f['is_active']);
                $deletedName = $f['filename'];
                $deletedType = $f['type'] ?? 'mst_pgw';
                if (file_exists($f['path']) && str_starts_with($f['path'], storage_path('app/simgaji'))) {
                    @unlink($f['path']);
                }
            } else {
                $newFiles[] = $f;
            }
        }

        if ($wasActive && ! empty($newFiles)) {
            foreach ($newFiles as &$nf) {
                if (($nf['type'] ?? 'mst_pgw') === $deletedType) {
                    $nf['is_active'] = true;
                    break;
                }
            }
        }

        $this->saveDbfManifest($newFiles);
        Cache::forget('rekonsiliasi_simgaji_data');

        return redirect()->back()->with('success', "File database '{$deletedName}' berhasil dihapus.");
    }

    private function getDbfManifest(): array
    {
        $manifestPath = storage_path('app/simgaji/manifest.json');
        $files = [];

        if (file_exists($manifestPath)) {
            $files = json_decode(file_get_contents($manifestPath), true) ?: [];
        }

        // Pastikan setiap file memiliki atribut 'type'
        $modified = false;
        foreach ($files as &$f) {
            if (empty($f['type'])) {
                $f['type'] = str_contains(strtoupper($f['filename'] ?? ''), 'HIS_GPOK') ? 'his_gpok' : 'mst_pgw';
                $modified = true;
            }
        }

        // Cek apakah default MST_PGW sudah terdaftar
        $hasMst = false;
        $hasHis = false;
        foreach ($files as $f) {
            if (($f['type'] ?? '') === 'mst_pgw') {
                $hasMst = true;
            }
            if (($f['type'] ?? '') === 'his_gpok') {
                $hasHis = true;
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
                $modified = true;
            }
        }

        if (! $hasHis) {
            $defaultHisFile = base_path('HIS_GPOK_2026-9-011600.DBF');
            if (file_exists($defaultHisFile)) {
                $files[] = [
                    'id' => 'default_his_gpok',
                    'type' => 'his_gpok',
                    'filename' => basename($defaultHisFile),
                    'stored_name' => basename($defaultHisFile),
                    'path' => $defaultHisFile,
                    'size' => round(filesize($defaultHisFile) / (1024 * 1024), 2).' MB',
                    'records' => 166812,
                    'keterangan' => 'Database Histori Gaji Pokok & SK Awal (Bawaan)',
                    'uploaded_at' => date('d M Y H:i', filemtime($defaultHisFile)),
                    'is_active' => true,
                ];
                $modified = true;
            }
        }

        if ($modified || ! file_exists($manifestPath)) {
            $this->saveDbfManifest($files);
        }

        return $files;
    }

    private function saveDbfManifest(array $files): void
    {
        $manifestPath = storage_path('app/simgaji/manifest.json');
        if (! is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0755, true);
        }
        file_put_contents($manifestPath, json_encode(array_values($files), JSON_PRETTY_PRINT));
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

    public function refreshCache()
    {
        $this->getReconciliationData(true);

        return redirect()->back()->with('success', 'Data rekonsiliasi SIMGAJI berhasil di-refresh.');
    }

    public function exportExcel(Request $request)
    {
        $activeTab = $request->get('tab', 'aktif_baru');
        $search = $request->get('search', '');
        $statusPensiun = $request->get('status_pensiun', 'semua');
        $statusSk = $request->get('status_sk', 'semua');
        $data = $this->getReconciliationData();

        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data[$activeTab] ?? []);

        if ($activeTab === 'beda_pangkat') {
            if ($statusPensiun === 'aktif') {
                $items = $items->filter(fn ($item) => empty($item['is_pensiun']));
            } elseif ($statusPensiun === 'pensiun') {
                $items = $items->filter(fn ($item) => ! empty($item['is_pensiun']));
            }

            if ($statusSk === 'belum_diinput') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'belum_diinput');
            } elseif ($statusSk === 'sudah_terjadwal') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'sudah_terjadwal');
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_tpp'] ?? ''), $searchLower);
            });
        }

        $tabTitles = [
            'aktif_baru' => 'Pegawai Baru di SIMGAJI',
            'beda_pangkat' => 'Perbedaan Golongan Pangkat',
            'beda_skpd' => 'Perbedaan Penempatan SKPD',
            'beda_jabatan' => 'Perbedaan Jabatan',
            'beda_lahir' => 'Perbedaan Tanggal Lahir',
            'pensiunan' => 'Arsip Pegawai Pensiun',
            'paruh_waktu' => 'PPPK Paruh Waktu',
        ];

        $tabTitle = $tabTitles[$activeTab] ?? strtoupper(str_replace('_', ' ', $activeTab));
        $activeFile = $this->getActiveDbfFile('mst_pgw');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $cleanTitle = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $tabTitle), 0, 31);
        $sheet->setTitle($cleanTitle ?: 'Rekonsiliasi');

        // Header Dokumen
        $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI KALIMANTAN SELATAN');
        $sheet->setCellValue('A2', 'LAPORAN REKONSILIASI DATABASE MASTER SIMGAJI');
        $keteranganSub = 'Kategori: '.$tabTitle.' | Database Acuan: '.($activeFile['filename'] ?? '-').' | Diunduh: '.date('d/m/Y H:i').' WITA';
        if ($activeTab === 'beda_pangkat') {
            $statusLabel = $statusPensiun === 'aktif' ? 'Hanya Pegawai Aktif' : ($statusPensiun === 'pensiun' ? 'Hanya Pensiunan' : 'Semua Status (Aktif & Pensiun)');
            $keteranganSub .= ' | Filter Status: '.$statusLabel;
            if ($statusSk === 'belum_diinput') {
                $keteranganSub .= ' | Filter SK: Belum Diinput di SIMGAJI';
            } elseif ($statusSk === 'sudah_terjadwal') {
                $keteranganSub .= ' | Filter SK: Sudah Terjadwal di SIMGAJI';
            }
        }
        $sheet->setCellValue('A3', $keteranganSub);

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true);

        // Header Tabel Kolom
        $rowNum = 5;
        $headers = [];
        if ($activeTab === 'aktif_baru') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'Gol/Ruang', 'Kode SKPD', 'Satker SIMGAJI', 'Instansi Inputer', 'TMT Stop'];
        } elseif ($activeTab === 'beda_pangkat') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD / Unit Kerja', 'Golru di Aplikasi', 'Golru di SIMGAJI', 'Status SK SIMGAJI', 'No. SK Terjadwal', 'TMT Pembayaran Gaji', 'Status Kepegawaian'];
        } elseif ($activeTab === 'beda_skpd') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD di Master Aplikasi', 'Satker Aplikasi', 'SKPD di SIMGAJI', 'Kode Satker SIMGAJI', 'SKPD di TPP'];
        } elseif ($activeTab === 'beda_jabatan') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD', 'Jabatan di Master Aplikasi', 'Jabatan di Pembayaran TPP', 'Status SIMGAJI'];
        } elseif ($activeTab === 'beda_lahir') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD / Unit Kerja', 'Tanggal Lahir di Aplikasi', 'Tanggal Lahir di SIMGAJI'];
        } elseif ($activeTab === 'pensiunan') {
            $headers = ['No', 'NIP', 'Nama Pensiunan / Mantan Pegawai', 'Golru Terakhir', 'TMT Berhenti / Pensiun', 'Instansi Terakhir (Inputer)', 'Status'];
        } elseif ($activeTab === 'paruh_waktu') {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'SKPD / Unit Kerja', 'Gol/Ruang', 'Status Pembayaran'];
        } else {
            $headers = ['No', 'NIP', 'Nama Pegawai', 'Gol/Ruang', 'Status', 'TMT Berhenti'];
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

        $lastCol = chr(ord('A') + count($headers) - 1);
        $startDataRow = $rowNum + 1;
        $rowNum++;

        $no = 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A'.$rowNum, $no++);
            $sheet->setCellValueExplicit('B'.$rowNum, (string) ($item['nip'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$rowNum, $item['nama'] ?? '');

            if ($activeTab === 'aktif_baru') {
                $sheet->setCellValue('D'.$rowNum, $item['golru_converted'] ?? '-');
                $sheet->setCellValueExplicit('E'.$rowNum, (string) ($item['kdskpd'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F'.$rowNum, (string) ($item['kdsatker'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('G'.$rowNum, $item['inputer'] ?? '-');
                $sheet->setCellValue('H'.$rowNum, $item['tmtstop'] ?: '-');
            } elseif ($activeTab === 'beda_pangkat') {
                $sheet->setCellValue('D'.$rowNum, $item['skpd'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['golru_app'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['pangkat_simgaji'] ?? '-');
                $statusSkText = ($item['status_sk'] ?? '') === 'sudah_terjadwal' ? 'Sudah Terjadwal di SIMGAJI' : 'Belum Diinput di SIMGAJI';
                $sheet->setCellValue('G'.$rowNum, $statusSkText);
                $sheet->setCellValue('H'.$rowNum, $item['sk_info']['nomorskep'] ?? '-');
                $sheet->setCellValue('I'.$rowNum, ! empty($item['sk_info']['tmtgaji']) ? date('d/m/Y', strtotime($item['sk_info']['tmtgaji'])) : '-');
                $sheet->setCellValue('J'.$rowNum, $item['status_kepegawaian'] ?? ($item['is_pensiun'] ? 'Pensiun' : 'Aktif'));
            } elseif ($activeTab === 'beda_skpd') {
                $sheet->setCellValue('D'.$rowNum, $item['skpd_app'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['satker_app'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['skpd_simgaji'] ?? '-');
                $sheet->setCellValueExplicit('G'.$rowNum, (string) ($item['kdsatker_simgaji'] ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValue('H'.$rowNum, $item['skpd_tpp'] ?? '-');
            } elseif ($activeTab === 'beda_jabatan') {
                $sheet->setCellValue('D'.$rowNum, $item['skpd'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['jabatan_app'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['jabatan_tpp'] ?? '-');
                $sheet->setCellValue('G'.$rowNum, $item['status_simgaji'] ?? '-');
            } elseif ($activeTab === 'beda_lahir') {
                $sheet->setCellValue('D'.$rowNum, $item['skpd'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['tgl_app'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['tgl_simgaji'] ?? '-');
            } elseif ($activeTab === 'pensiunan') {
                $sheet->setCellValue('D'.$rowNum, $item['golru'] ?? ($item['golru_converted'] ?? '-'));
                $sheet->setCellValue('E'.$rowNum, $item['tmtstop'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['inputer'] ?? '-');
                $sheet->setCellValue('G'.$rowNum, $item['status'] ?? '-');
            } elseif ($activeTab === 'paruh_waktu') {
                $sheet->setCellValue('D'.$rowNum, $item['skpd'] ?? '-');
                $sheet->setCellValue('E'.$rowNum, $item['golru'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['status'] ?? '-');
            } else {
                $sheet->setCellValue('D'.$rowNum, $item['golru'] ?? ($item['golru_converted'] ?? '-'));
                $sheet->setCellValue('E'.$rowNum, $item['status'] ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item['tmtstop'] ?? '-');
            }

            $rowNum++;
        }

        $endDataRow = max($startDataRow, $rowNum - 1);

        // Styling Border dan Alignment
        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
        $sheet->getStyle('A5:'.$lastCol.$endDataRow)->applyFromArray($styleBorder);
        $sheet->getStyle('A'.$startDataRow.':A'.$endDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B'.$startDataRow.':B'.$endDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-fit Column Widths
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekonsiliasi_SIMGAJI_'.ucfirst($activeTab).'_'.date('Ymd_His').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        $activeTab = $request->get('tab', 'aktif_baru');
        $search = $request->get('search', '');
        $statusPensiun = $request->get('status_pensiun', 'semua');
        $statusSk = $request->get('status_sk', 'semua');
        $data = $this->getReconciliationData();

        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $items = collect($data[$activeTab] ?? []);

        if ($activeTab === 'beda_pangkat') {
            if ($statusPensiun === 'aktif') {
                $items = $items->filter(fn ($item) => empty($item['is_pensiun']));
            } elseif ($statusPensiun === 'pensiun') {
                $items = $items->filter(fn ($item) => ! empty($item['is_pensiun']));
            }

            if ($statusSk === 'belum_diinput') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'belum_diinput');
            } elseif ($statusSk === 'sudah_terjadwal') {
                $items = $items->filter(fn ($item) => ($item['status_sk'] ?? '') === 'sudah_terjadwal');
            }
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $items = $items->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['nip'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['nama'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['skpd_simgaji'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_app'] ?? ''), $searchLower)
                    || str_contains(strtolower($item['jabatan_tpp'] ?? ''), $searchLower);
            });
        }

        $tabTitles = [
            'aktif_baru' => 'Pegawai Aktif Baru di SIMGAJI',
            'beda_pangkat' => 'Perbedaan Golongan / Pangkat',
            'beda_skpd' => 'Perbedaan Penempatan SKPD',
            'beda_jabatan' => 'Perbedaan Jabatan',
            'beda_lahir' => 'Perbedaan Tanggal Lahir',
            'pensiunan' => 'Arsip Pegawai Pensiun / Berhenti',
            'paruh_waktu' => 'PPPK Paruh Waktu',
        ];

        $tabTitle = $tabTitles[$activeTab] ?? strtoupper(str_replace('_', ' ', $activeTab));
        $activeFile = $this->getActiveDbfFile('mst_pgw');

        $pdf = Pdf::loadView('laporan.rekonsiliasi_simgaji.pdf', [
            'items' => $items,
            'activeTab' => $activeTab,
            'tabTitle' => $tabTitle,
            'summary' => $data['summary'],
            'activeFile' => $activeFile,
            'search' => $search,
            'statusPensiun' => $statusPensiun,
            'statusSk' => $statusSk,
        ])->setPaper('a4', 'landscape');

        $filename = 'Laporan_Rekonsiliasi_'.ucfirst($activeTab).'_'.date('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    private function getReconciliationData(bool $forceRefresh = false): array
    {
        $cacheKey = 'rekonsiliasi_simgaji_data';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $activeDbf = $this->getActiveDbfFile('mst_pgw');
        $dbfPath = $activeDbf ? $activeDbf['path'] : null;

        if (! $dbfPath || ! file_exists($dbfPath)) {
            $rootCandidates = glob(base_path('MST_PGW*.DBF')) ?: (glob(base_path('mst_pgw*.dbf')) ?: glob(base_path('*.DBF')));
            if (! empty($rootCandidates) && file_exists($rootCandidates[0])) {
                $dbfPath = $rootCandidates[0];
            } else {
                $dbfPath = base_path('MST_PGW_2026-9-011600.DBF');
            }
        }

        if (! file_exists($dbfPath)) {
            return ['error' => 'File database SIMGAJI belum ditemukan. Silakan klik menu "Upload Master SIMGAJI" untuk mengunggah file database master pegawai SIMGAJI (.DBF).'];
        }

        // Baca file riwayat HIS_GPOK jika ada
        $activeHis = $this->getActiveDbfFile('his_gpok');
        $hisDbfPath = $activeHis ? $activeHis['path'] : null;
        if (! $hisDbfPath || ! file_exists($hisDbfPath)) {
            $hisCandidates = glob(base_path('HIS_GPOK*.DBF')) ?: (glob(base_path('his_gpok*.dbf')) ?: []);
            if (! empty($hisCandidates) && file_exists($hisCandidates[0])) {
                $hisDbfPath = $hisCandidates[0];
            }
        }

        $hisPangkatMap = [];
        if ($hisDbfPath && file_exists($hisDbfPath)) {
            try {
                $tableHis = new TableReader($hisDbfPath, ['encoding' => 'cp850']);
                while ($rh = $tableHis->nextRecord()) {
                    $hnip = trim((string) $rh->get('nip'));
                    if (! $hnip) {
                        continue;
                    }
                    $hRawPangkat = strtoupper(trim((string) $rh->get('kdpangkat')));
                    $hConvPangkat = $this->mapPangkat[$hRawPangkat] ?? $hRawPangkat;
                    $htmt = trim((string) $rh->get('tmt'));
                    $htmtgaji = trim((string) $rh->get('tmtgaji'));
                    $htglupdate = trim((string) $rh->get('tglupdate'));
                    $hNoSk = trim((string) $rh->get('nomorskep'));
                    $hTglSk = trim((string) $rh->get('tglskep'));
                    $hPenerbit = trim((string) $rh->get('penerbitsk'));
                    $hGapok = trim((string) $rh->get('gapok'));
                    $hKet = trim((string) $rh->get('keterangan'));

                    $hKeyDate = $htmt ?: ($htmtgaji ?: $htglupdate);
                    if (! isset($hisPangkatMap[$hnip]) || $hKeyDate > ($hisPangkatMap[$hnip]['keyDate'] ?? '')) {
                        $hisPangkatMap[$hnip] = [
                            'kdpangkat' => $hRawPangkat,
                            'kdpangkat_converted' => $hConvPangkat,
                            'nomorskep' => $hNoSk,
                            'tglskep' => $hTglSk,
                            'penerbitsk' => $hPenerbit,
                            'tmt' => $htmt,
                            'tmtgaji' => $htmtgaji,
                            'gapok' => $hGapok,
                            'keterangan' => $hKet,
                            'keyDate' => $hKeyDate,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika ada kegagalan membaca file histori
            }
        }

        try {
            $table = new TableReader($dbfPath);
            $totalDbf = $table->getRecordCount();

            // Load Pegawai DB beserta Jabatan & UnitKerja
            $dbPegawais = Pegawai::with(['unitKerja:id,skpd,satker', 'jabatan:id,nama,eselon,jenis'])
                ->select('id', 'nip', 'nama', 'golru', 'status_pegawai', 'tgl_lahir', 'unit_kerja_id', 'jabatan_id')
                ->get()
                ->keyBy('nip');

            // Load Data Realisasi TPP untuk verifikasi nama jabatan & SKPD penggajian riil
            $tppMap = RealisasiTpp::select('pegawai_id', 'raw_data')
                ->latest('id')
                ->get()
                ->keyBy('pegawai_id');

            $allDbf = [];
            $today = date('Y-m-d');

            while ($r = $table->nextRecord()) {
                $nip = trim($r->get('nip'));
                if (! $nip) {
                    continue;
                }

                $rawPangkat = strtoupper(trim((string) $r->get('kdpangkat')));
                $convertedPangkat = $this->mapPangkat[$rawPangkat] ?? $rawPangkat;

                $allDbf[$nip] = [
                    'nip' => $nip,
                    'nama' => trim($r->get('nama')),
                    'kdpangkat' => $rawPangkat,
                    'golru_converted' => $convertedPangkat,
                    'kdstapeg' => trim((string) $r->get('kdstapeg')),
                    'kdskpd' => trim((string) $r->get('kdskpd')),
                    'kdsatker' => trim((string) $r->get('kdsatker')),
                    'inputer' => trim((string) $r->get('inputer')),
                    'tmtstop' => trim((string) $r->get('tmtstop')),
                    'tgllhr' => trim((string) $r->get('tgllhr')),
                    'tempatlhr' => trim((string) $r->get('tempatlhr')),
                    'kdjenkel' => trim((string) $r->get('kdjenkel')),
                    'pendidikan' => trim((string) $r->get('pendidikan')),
                    'masker' => (int) $r->get('masker'),
                    'kdeselon' => trim((string) $r->get('kdeselon')),
                    'tjeselon' => (float) $r->get('tjeselon'),
                    'tjfungsi' => (float) $r->get('tjfungsi'),
                    'kdfungsi' => trim((string) $r->get('kdfungsi')),
                    'kdguru' => trim((string) $r->get('kdguru')),
                ];
            }

            $aktifBaru = [];
            $pensiunan = [];
            $bedaPangkat = [];
            $bedaSkpd = [];
            $bedaJabatan = [];
            $bedaLahir = [];
            $paruhWaktu = [];
            $inBothCount = 0;

            foreach ($allDbf as $nip => $d) {
                if (! isset($dbPegawais[$nip])) {
                    $ts = $d['tmtstop'];
                    if (! $ts || $ts === '0000-00-00' || $ts > $today) {
                        $aktifBaru[] = $d;
                    } else {
                        $pensiunan[] = [
                            'nip' => $nip,
                            'nama' => $d['nama'],
                            'golru_converted' => $d['golru_converted'],
                            'tmtstop' => $ts,
                            'inputer' => $d['inputer'],
                            'status' => 'Pensiun / Berhenti',
                        ];
                    }
                } else {
                    $inBothCount++;
                    $db = $dbPegawais[$nip];
                    $tppItem = $tppMap[$db->id] ?? null;
                    $tppRaw = $tppItem ? ($tppItem->raw_data ?? []) : [];

                    // 1. Cek beda pangkat (memperhitungkan ekuivalensi Romawi PPPK)
                    if (! $this->isSamePangkat($db->golru, $d['golru_converted'], $d['kdpangkat'])) {
                        $stapeg = trim((string) $d['kdstapeg']);
                        $ts = trim((string) $d['tmtstop']);
                        $isPensiun = in_array($stapeg, ['22', '23', '24', '27', '28'])
                            || ($ts && $ts !== '0000-00-00' && $ts <= $today);

                        $ketPensiun = 'Aktif';
                        if ($isPensiun) {
                            if ($stapeg === '23') {
                                $ketPensiun = 'Pensiun (BUP)';
                            } elseif ($stapeg === '22') {
                                $ketPensiun = 'Pensiun Sendiri';
                            } elseif ($stapeg === '24') {
                                $ketPensiun = 'Pensiun Janda/Duda';
                            } elseif ($ts && $ts <= $today) {
                                $ketPensiun = 'Berhenti (TMT: '.$ts.')';
                            } else {
                                $ketPensiun = 'Pensiun';
                            }
                        }

                        // Verifikasi silang ke database riwayat HIS_GPOK
                        $nipStr = (string) $nip;
                        $statusSk = 'belum_diinput';
                        $skInfo = null;

                        if (isset($hisPangkatMap[$nipStr])) {
                            $his = $hisPangkatMap[$nipStr];
                            if ($this->isSamePangkat($db->golru, $his['kdpangkat_converted'], $his['kdpangkat'])) {
                                $statusSk = 'sudah_terjadwal';
                                $skInfo = [
                                    'nomorskep' => $his['nomorskep'],
                                    'tglskep' => $his['tglskep'],
                                    'penerbitsk' => $his['penerbitsk'],
                                    'tmt' => $his['tmt'],
                                    'tmtgaji' => $his['tmtgaji'],
                                    'gapok' => $his['gapok'],
                                    'keterangan' => $his['keterangan'],
                                    'pangkat_his' => $his['kdpangkat_converted'],
                                ];
                            }
                        }

                        $bedaPangkat[] = [
                            'nip' => $nip,
                            'nama' => $db->nama,
                            'golru_app' => $db->golru ?: '-',
                            'pangkat_simgaji' => $d['golru_converted'],
                            'pangkat_raw_dbf' => $d['kdpangkat'],
                            'skpd' => $db->unitKerja ? $db->unitKerja->skpd : '-',
                            'is_pensiun' => $isPensiun,
                            'status_kepegawaian' => $ketPensiun,
                            'tmtstop' => $ts ?: null,
                            'kdstapeg' => $stapeg ?: null,
                            'status_sk' => $statusSk,
                            'sk_info' => $skInfo,
                        ];
                    }

                    // 2. Cek beda SKPD (SIMGAJI vs Master Pegawai)
                    $simgajiSkpd = $this->skpdCodeMap[$d['kdskpd']] ?? null;
                    $appSkpd = $db->unitKerja ? $db->unitKerja->skpd : '-';
                    $appSatker = $db->unitKerja ? $db->unitKerja->satker : '-';
                    $tppSkpd = trim($tppRaw['Instansi / UPT'] ?? '');

                    if ($simgajiSkpd && strcasecmp(trim($simgajiSkpd), trim($appSkpd)) !== 0) {
                        $bedaSkpd[] = [
                            'nip' => $nip,
                            'nama' => $db->nama,
                            'skpd_app' => $appSkpd,
                            'satker_app' => $appSatker,
                            'skpd_simgaji' => $simgajiSkpd,
                            'kdskpd_simgaji' => $d['kdskpd'],
                            'kdsatker_simgaji' => $d['kdsatker'],
                            'inputer_simgaji' => $d['inputer'],
                            'skpd_tpp' => $tppSkpd ?: '-',
                        ];
                    }

                    // 3. Cek beda Jabatan (Master Pegawai vs Penggajian TPP & Info SIMGAJI)
                    $appJabatan = $db->jabatan ? trim($db->jabatan->nama) : '-';
                    $tppJabatan = trim($tppRaw['Jabatan'] ?? '');

                    $eselonDbf = trim((string) $d['kdeselon']);
                    $tjEselon = (float) $d['tjeselon'];
                    $tjFungsi = (float) $d['tjfungsi'];

                    $statusDbf = 'Fungsional Umum / Pelaksana';
                    if ($eselonDbf && $eselonDbf !== '00') {
                        $statusDbf = 'Struktural (Eselon '.$eselonDbf.')';
                    } elseif ($tjFungsi > 0) {
                        $statusDbf = 'Fungsional (Tunjangan Rp '.number_format($tjFungsi, 0, ',', '.').')';
                    }

                    if ($tppJabatan && strcasecmp($appJabatan, $tppJabatan) !== 0) {
                        $bedaJabatan[] = [
                            'nip' => $nip,
                            'nama' => $db->nama,
                            'skpd' => $appSkpd,
                            'jabatan_app' => $appJabatan,
                            'jabatan_tpp' => $tppJabatan,
                            'status_simgaji' => $statusDbf,
                            'eselon_dbf' => $eselonDbf ?: '-',
                        ];
                    }

                    // 4. Cek beda tanggal lahir
                    if (! empty($d['tgllhr']) && ! empty($db->tgl_lahir)) {
                        $tglDbf = date('Y-m-d', strtotime($d['tgllhr']));
                        $tglDb = date('Y-m-d', strtotime($db->tgl_lahir));
                        if ($tglDbf !== $tglDb) {
                            $bedaLahir[] = [
                                'nip' => $nip,
                                'nama' => $db->nama,
                                'tgl_app' => $tglDb,
                                'tgl_simgaji' => $tglDbf,
                                'skpd' => $db->unitKerja ? $db->unitKerja->skpd : '-',
                            ];
                        }
                    }
                }
            }

            // PPPK Paruh Waktu dari Aplikasi
            foreach ($dbPegawais as $nip => $p) {
                if (strtoupper((string) $p->status_pegawai) === 'PPPK PARUH WAKTU') {
                    $paruhWaktu[] = [
                        'nip' => $p->nip,
                        'nama' => $p->nama,
                        'golru' => $p->golru ?: '-',
                        'status' => $p->status_pegawai,
                        'skpd' => $p->unitKerja ? $p->unitKerja->skpd : '-',
                    ];
                }
            }

            $bedaPangkatAktifCount = count(array_filter($bedaPangkat, fn ($x) => empty($x['is_pensiun'])));
            $bedaPangkatPensiunCount = count(array_filter($bedaPangkat, fn ($x) => ! empty($x['is_pensiun'])));
            $bedaPangkatBelumDiinputCount = count(array_filter($bedaPangkat, fn ($x) => ($x['status_sk'] ?? '') === 'belum_diinput'));
            $bedaPangkatTerjadwalCount = count(array_filter($bedaPangkat, fn ($x) => ($x['status_sk'] ?? '') === 'sudah_terjadwal'));

            $result = [
                'summary' => [
                    'total_dbf' => $totalDbf,
                    'total_app' => $dbPegawais->count(),
                    'in_both' => $inBothCount,
                    'aktif_baru_count' => count($aktifBaru),
                    'beda_pangkat_count' => count($bedaPangkat),
                    'beda_pangkat_aktif_count' => $bedaPangkatAktifCount,
                    'beda_pangkat_pensiun_count' => $bedaPangkatPensiunCount,
                    'beda_pangkat_belum_diinput_count' => $bedaPangkatBelumDiinputCount,
                    'beda_pangkat_terjadwal_count' => $bedaPangkatTerjadwalCount,
                    'beda_skpd_count' => count($bedaSkpd),
                    'beda_jabatan_count' => count($bedaJabatan),
                    'beda_lahir_count' => count($bedaLahir),
                    'pensiunan_count' => count($pensiunan),
                    'paruh_waktu_count' => count($paruhWaktu),
                    'active_mst_filename' => $activeDbf ? $activeDbf['filename'] : basename($dbfPath),
                    'active_his_filename' => $activeHis ? $activeHis['filename'] : ($hisDbfPath ? basename($hisDbfPath) : null),
                ],
                'aktif_baru' => $aktifBaru,
                'beda_pangkat' => $bedaPangkat,
                'beda_skpd' => $bedaSkpd,
                'beda_jabatan' => $bedaJabatan,
                'beda_lahir' => $bedaLahir,
                'pensiunan' => $pensiunan,
                'paruh_waktu' => $paruhWaktu,
            ];

            Cache::put($cacheKey, $result, now()->addHours(2));

            return $result;
        } catch (\Exception $e) {
            return ['error' => 'Gagal membaca database SIMGAJI: '.$e->getMessage()];
        }
    }
}
