<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SikdCoreController extends Controller
{
    private function getReportData($periode)
    {
        $pegawais = Pegawai::with(['realisasiGajis' => function ($q) use ($periode) {
            $q->where('periode', $periode);
        }])->get();

        $grouped = $pegawais->groupBy('golru');

        $laporan = [];
        $totals = [
            'jumlah' => 0, 'gaji_pokok' => 0, 'tunj_keluarga' => 0, 'tunj_struktural' => 0,
            'tunj_fungsional' => 0, 'tunj_umum' => 0, 'tunj_pph' => 0, 'tunj_beras' => 0,
            'tunj_lainnya' => 0, 'pembulatan' => 0, 'gaji_kotor' => 0, 'tunj_perbaikan' => 0,
            'total_penghasilan' => 0,
        ];

        $golongans = $grouped->keys()->sort();

        foreach ($golongans as $gol) {
            $pegs = $grouped[$gol];

            $row = [
                'golongan' => $gol ?: 'TIDAK DIKETAHUI', 'jumlah' => 0, 'gaji_pokok' => 0,
                'tunj_keluarga' => 0, 'tunj_struktural' => 0, 'tunj_fungsional' => 0, 'tunj_umum' => 0,
                'tunj_pph' => 0, 'tunj_beras' => 0, 'tunj_lainnya' => 0, 'pembulatan' => 0,
                'gaji_kotor' => 0, 'tunj_perbaikan' => 0, 'total_penghasilan' => 0,
            ];

            foreach ($pegs as $peg) {
                $gaji = $peg->realisasiGajis->first();
                if ($gaji && $gaji->raw_data) {
                    $raw = is_string($gaji->raw_data) ? json_decode($gaji->raw_data, true) : $gaji->raw_data;

                    if (is_array($raw)) {
                        $row['jumlah'] += 1;
                        $row['gaji_pokok'] += (float) ($raw['gapok'] ?? 0);
                        $row['tunj_keluarga'] += (float) ($raw['tjistri'] ?? 0) + (float) ($raw['tjanak'] ?? 0);
                        $row['tunj_struktural'] += (float) ($raw['tjstruk'] ?? 0) + (float) ($raw['tjeselon'] ?? 0);
                        $row['tunj_fungsional'] += (float) ($raw['tjfungsi'] ?? 0);
                        $row['tunj_umum'] += (float) ($raw['tjumum'] ?? 0);
                        $row['tunj_pph'] += (float) ($raw['tjpajak'] ?? 0);
                        $row['tunj_beras'] += (float) ($raw['tjberas'] ?? 0);

                        $tunjLain = (float) ($raw['tjkk'] ?? 0) + (float) ($raw['tjkm'] ?? 0) +
                                    (float) ($raw['tjkhusus'] ?? 0) + (float) ($raw['tjterpenci'] ?? 0) +
                                    (float) ($raw['tjaskes'] ?? 0);

                        $row['tunj_lainnya'] += $tunjLain;
                        $row['pembulatan'] += (float) ($raw['tbulat'] ?? 0);

                        $gajiKotor = $row['gaji_pokok'] + $row['tunj_keluarga'] + $row['tunj_struktural'] +
                                     $row['tunj_fungsional'] + $row['tunj_umum'] + $row['tunj_pph'] +
                                     $row['tunj_beras'] + $row['tunj_lainnya'] + $row['pembulatan'];

                        $row['tunj_perbaikan'] += (float) ($raw['tjtpp'] ?? 0);
                        $row['gaji_kotor'] += (float) ($raw['kotor'] ?? $gajiKotor);
                        $row['total_penghasilan'] += (float) ($raw['kotor'] ?? $gajiKotor) + (float) ($raw['tjtpp'] ?? 0);
                    }
                }
            }

            if ($row['jumlah'] > 0) {
                $laporan[] = $row;
                foreach ($totals as $key => $val) {
                    $totals[$key] += $row[$key];
                }
            }
        }

        return ['laporan' => $laporan, 'totals' => $totals];
    }

    public function index(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);

        return view('laporan.sikd-core.index', [
            'laporan' => $data['laporan'],
            'totals' => $data['totals'],
            'periode' => $periode,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);

        $pdf = Pdf::loadView('laporan.sikd-core.pdf', [
            'laporan' => $data['laporan'],
            'totals' => $data['totals'],
            'periode' => $periode,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_SIKD_Core_'.str_replace(' ', '_', $periode).'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);
        $laporan = $data['laporan'];
        $totals = $data['totals'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A1', 'No');
        $sheet->mergeCells('B1:B2');
        $sheet->setCellValue('B1', 'Golongan');
        $sheet->mergeCells('C1:C2');
        $sheet->setCellValue('C1', 'Jumlah');
        $sheet->mergeCells('D1:D2');
        $sheet->setCellValue('D1', "Gaji\nPokok");
        $sheet->mergeCells('E1:E2');
        $sheet->setCellValue('E1', "Tunjangan\nKeluarga");
        $sheet->mergeCells('F1:G1');
        $sheet->setCellValue('F1', 'Tunjangan Jabatan');
        $sheet->setCellValue('F2', 'Struktural');
        $sheet->setCellValue('G2', 'Fungsional');
        $sheet->mergeCells('H1:H2');
        $sheet->setCellValue('H1', "Tunjangan\nUmum");
        $sheet->mergeCells('I1:I2');
        $sheet->setCellValue('I1', "Tunjangan\nPPH");
        $sheet->mergeCells('J1:J2');
        $sheet->setCellValue('J1', "Tunjangan\nBeras");
        $sheet->mergeCells('K1:K2');
        $sheet->setCellValue('K1', "Tunjangan\nLainnya");
        $sheet->mergeCells('L1:L2');
        $sheet->setCellValue('L1', "Lain-lain\nPembulatan");
        $sheet->mergeCells('M1:M2');
        $sheet->setCellValue('M1', "Gaji\nKotor");
        $sheet->mergeCells('N1:N2');
        $sheet->setCellValue('N1', "Tunjangan\nPerbaikan\nPenghasilan");
        $sheet->mergeCells('O1:O2');
        $sheet->setCellValue('O1', "Kode\nBayar");
        $sheet->mergeCells('P1:P2');
        $sheet->setCellValue('P1', "Total\nPenghasilan");

        $sheet->setCellValue('A3', '(1)');
        $sheet->setCellValue('B3', '(2)');
        $sheet->setCellValue('C3', '(3)');
        $sheet->setCellValue('D3', '(4)');
        $sheet->setCellValue('E3', '(5)');
        $sheet->setCellValue('F3', '(6)');
        $sheet->setCellValue('G3', '(7)');
        $sheet->setCellValue('H3', '(8)');
        $sheet->setCellValue('I3', '(9)');
        $sheet->setCellValue('J3', '(10)');
        $sheet->setCellValue('K3', '(11)');
        $sheet->setCellValue('L3', '(12)');
        $sheet->setCellValue('M3', '(13)=(4)+..+(12)');
        $sheet->setCellValue('N3', '(14)');
        $sheet->setCellValue('O3', '(15)');
        $sheet->setCellValue('P3', '(16)=(13)+(14)');

        $sheet->getStyle('A1:P3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:P3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:P3')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:P3')->getFont()->setBold(true);

        $row = 4;
        $no = 1;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $item['golongan']);
            $sheet->setCellValue('C'.$row, $item['jumlah']);
            $sheet->setCellValue('D'.$row, $item['gaji_pokok']);
            $sheet->setCellValue('E'.$row, $item['tunj_keluarga']);
            $sheet->setCellValue('F'.$row, $item['tunj_struktural']);
            $sheet->setCellValue('G'.$row, $item['tunj_fungsional']);
            $sheet->setCellValue('H'.$row, $item['tunj_umum']);
            $sheet->setCellValue('I'.$row, $item['tunj_pph']);
            $sheet->setCellValue('J'.$row, $item['tunj_beras']);
            $sheet->setCellValue('K'.$row, $item['tunj_lainnya']);
            $sheet->setCellValue('L'.$row, $item['pembulatan']);
            $sheet->setCellValue('M'.$row, $item['gaji_kotor']);
            $sheet->setCellValue('N'.$row, $item['tunj_perbaikan']);
            $sheet->setCellValue('O'.$row, '-');
            $sheet->setCellValue('P'.$row, $item['total_penghasilan']);
            $row++;
        }

        if (count($laporan) > 0) {
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, 'Jumlah');
            $sheet->setCellValue('C'.$row, $totals['jumlah']);
            $sheet->setCellValue('D'.$row, $totals['gaji_pokok']);
            $sheet->setCellValue('E'.$row, $totals['tunj_keluarga']);
            $sheet->setCellValue('F'.$row, $totals['tunj_struktural']);
            $sheet->setCellValue('G'.$row, $totals['tunj_fungsional']);
            $sheet->setCellValue('H'.$row, $totals['tunj_umum']);
            $sheet->setCellValue('I'.$row, $totals['tunj_pph']);
            $sheet->setCellValue('J'.$row, $totals['tunj_beras']);
            $sheet->setCellValue('K'.$row, $totals['tunj_lainnya']);
            $sheet->setCellValue('L'.$row, $totals['pembulatan']);
            $sheet->setCellValue('M'.$row, $totals['gaji_kotor']);
            $sheet->setCellValue('N'.$row, $totals['tunj_perbaikan']);
            $sheet->setCellValue('O'.$row, '');
            $sheet->setCellValue('P'.$row, $totals['total_penghasilan']);
            $sheet->getStyle('A'.$row.':P'.$row)->getFont()->setBold(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:P'.($row - 1))->applyFromArray($styleArray);

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_SIKD_Core_'.str_replace(' ', '_', $periode).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    private function getReportDataRinci($periode, $skpdFilter, $search = null, $golonganFilter = null)
    {
        $query = Pegawai::with(['unitKerja', 'realisasiGajis' => function ($q) use ($periode) {
            $q->where('periode', $periode);
        }]);

        if ($skpdFilter) {
            $query->whereHas('unitKerja', function ($q) use ($skpdFilter) {
                $q->where('skpd', $skpdFilter);
            });
        }

        if ($golonganFilter) {
            $query->where('golru', $golonganFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nip', 'like', '%'.$search.'%');
            });
        }

        $pegawais = $query->get();

        $laporan = [];
        $totals = [
            'gaji_pokok' => 0, 'tunj_keluarga' => 0, 'tunj_struktural' => 0,
            'tunj_fungsional' => 0, 'tunj_umum' => 0, 'tunj_pph' => 0, 'tunj_beras' => 0,
            'tunj_lainnya' => 0, 'pembulatan' => 0, 'gaji_kotor' => 0, 'tunj_perbaikan' => 0,
            'total_penghasilan' => 0,
        ];

        foreach ($pegawais as $peg) {
            $gaji = $peg->realisasiGajis->first();
            if ($gaji && $gaji->raw_data) {
                $raw = is_string($gaji->raw_data) ? json_decode($gaji->raw_data, true) : $gaji->raw_data;

                if (is_array($raw)) {
                    $tunj_keluarga = (float) ($raw['tjistri'] ?? 0) + (float) ($raw['tjanak'] ?? 0);
                    $tunj_struktural = (float) ($raw['tjstruk'] ?? 0) + (float) ($raw['tjeselon'] ?? 0);
                    $tunj_fungsional = (float) ($raw['tjfungsi'] ?? 0);
                    $tunj_umum = (float) ($raw['tjumum'] ?? 0);
                    $tunj_pph = (float) ($raw['tjpajak'] ?? 0);
                    $tunj_beras = (float) ($raw['tjberas'] ?? 0);
                    $tunj_lainnya = (float) ($raw['tjkk'] ?? 0) + (float) ($raw['tjkm'] ?? 0) +
                                    (float) ($raw['tjkhusus'] ?? 0) + (float) ($raw['tjterpenci'] ?? 0) +
                                    (float) ($raw['tjaskes'] ?? 0);
                    $pembulatan = (float) ($raw['tbulat'] ?? 0);
                    $gaji_pokok = (float) ($raw['gapok'] ?? 0);

                    $gajiKotor = $gaji_pokok + $tunj_keluarga + $tunj_struktural +
                                 $tunj_fungsional + $tunj_umum + $tunj_pph +
                                 $tunj_beras + $tunj_lainnya + $pembulatan;

                    $tunj_perbaikan = (float) ($raw['tjtpp'] ?? 0);
                    $gajiKotorFinal = (float) ($raw['kotor'] ?? $gajiKotor);
                    $total_penghasilan = $gajiKotorFinal + $tunj_perbaikan;

                    $row = [
                        'nip' => $peg->nip,
                        'nama' => $peg->nama,
                        'skpd' => $peg->unitKerja ? $peg->unitKerja->skpd : '-',
                        'golongan' => $peg->golru ?: '-',
                        'gaji_pokok' => $gaji_pokok,
                        'tunj_keluarga' => $tunj_keluarga,
                        'tunj_struktural' => $tunj_struktural,
                        'tunj_fungsional' => $tunj_fungsional,
                        'tunj_umum' => $tunj_umum,
                        'tunj_pph' => $tunj_pph,
                        'tunj_beras' => $tunj_beras,
                        'tunj_lainnya' => $tunj_lainnya,
                        'pembulatan' => $pembulatan,
                        'gaji_kotor' => $gajiKotorFinal,
                        'tunj_perbaikan' => $tunj_perbaikan,
                        'total_penghasilan' => $total_penghasilan,
                    ];

                    $laporan[] = $row;

                    foreach ($totals as $key => $val) {
                        $totals[$key] += $row[$key];
                    }
                }
            }
        }

        return ['laporan' => $laporan, 'totals' => $totals];
    }

    public function rinci(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $skpdFilter = $request->input('skpd');
        $search = $request->input('search');
        $golonganFilter = $request->input('golongan');

        $skpdList = UnitKerja::select('skpd')->distinct()->whereNotNull('skpd')->orderBy('skpd')->pluck('skpd');
        $golonganList = Pegawai::select('golru')->distinct()->whereNotNull('golru')->orderBy('golru')->pluck('golru');

        $data = $this->getReportDataRinci($periode, $skpdFilter, $search, $golonganFilter);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 100;
        $laporanCollection = collect($data['laporan']);
        $items = $laporanCollection->slice(($page - 1) * $perPage, $perPage)->all();
        $paginatedLaporan = new LengthAwarePaginator($items, $laporanCollection->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);

        return view('laporan.sikd-core.rinci', [
            'laporan' => $paginatedLaporan,
            'totals' => $data['totals'],
            'periode' => $periode,
            'skpdFilter' => $skpdFilter,
            'skpdList' => $skpdList,
            'search' => $search,
            'golonganFilter' => $golonganFilter,
            'golonganList' => $golonganList,
        ]);
    }

    public function exportExcelRinci(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $skpdFilter = $request->input('skpd');
        $search = $request->input('search');
        $golonganFilter = $request->input('golongan');

        // This export might take memory, so limit time
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $data = $this->getReportDataRinci($periode, $skpdFilter, $search, $golonganFilter);
        $laporan = $data['laporan'];
        $totals = $data['totals'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A1', 'No');
        $sheet->mergeCells('B1:B2');
        $sheet->setCellValue('B1', 'NIP');
        $sheet->mergeCells('C1:C2');
        $sheet->setCellValue('C1', 'Nama Pegawai');
        $sheet->mergeCells('D1:D2');
        $sheet->setCellValue('D1', 'SKPD');
        $sheet->mergeCells('E1:E2');
        $sheet->setCellValue('E1', 'Golongan');
        $sheet->mergeCells('F1:F2');
        $sheet->setCellValue('F1', "Gaji\nPokok");
        $sheet->mergeCells('G1:G2');
        $sheet->setCellValue('G1', "Tunjangan\nKeluarga");
        $sheet->mergeCells('H1:I1');
        $sheet->setCellValue('H1', 'Tunjangan Jabatan');
        $sheet->setCellValue('H2', 'Struktural');
        $sheet->setCellValue('I2', 'Fungsional');
        $sheet->mergeCells('J1:J2');
        $sheet->setCellValue('J1', "Tunjangan\nUmum");
        $sheet->mergeCells('K1:K2');
        $sheet->setCellValue('K1', "Tunjangan\nPPH");
        $sheet->mergeCells('L1:L2');
        $sheet->setCellValue('L1', "Tunjangan\nBeras");
        $sheet->mergeCells('M1:M2');
        $sheet->setCellValue('M1', "Tunjangan\nLainnya");
        $sheet->mergeCells('N1:N2');
        $sheet->setCellValue('N1', "Lain-lain\nPembulatan");
        $sheet->mergeCells('O1:O2');
        $sheet->setCellValue('O1', "Gaji\nKotor");
        $sheet->mergeCells('P1:P2');
        $sheet->setCellValue('P1', "Tunjangan\nPerbaikan\nPenghasilan");
        $sheet->mergeCells('Q1:Q2');
        $sheet->setCellValue('Q1', "Kode\nBayar");
        $sheet->mergeCells('R1:R2');
        $sheet->setCellValue('R1', "Total\nPenghasilan");

        $sheet->getStyle('A1:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:R2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:R2')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:R2')->getFont()->setBold(true);

        $row = 3;
        $no = 1;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A'.$row, $no++);
            // Force string for NIP to prevent scientific notation
            $sheet->setCellValueExplicit('B'.$row, (string) $item['nip'], DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $item['nama']);
            $sheet->setCellValue('D'.$row, $item['skpd']);
            $sheet->setCellValue('E'.$row, $item['golongan']);
            $sheet->setCellValue('F'.$row, $item['gaji_pokok']);
            $sheet->setCellValue('G'.$row, $item['tunj_keluarga']);
            $sheet->setCellValue('H'.$row, $item['tunj_struktural']);
            $sheet->setCellValue('I'.$row, $item['tunj_fungsional']);
            $sheet->setCellValue('J'.$row, $item['tunj_umum']);
            $sheet->setCellValue('K'.$row, $item['tunj_pph']);
            $sheet->setCellValue('L'.$row, $item['tunj_beras']);
            $sheet->setCellValue('M'.$row, $item['tunj_lainnya']);
            $sheet->setCellValue('N'.$row, $item['pembulatan']);
            $sheet->setCellValue('O'.$row, $item['gaji_kotor']);
            $sheet->setCellValue('P'.$row, $item['tunj_perbaikan']);
            $sheet->setCellValue('Q'.$row, '-');
            $sheet->setCellValue('R'.$row, $item['total_penghasilan']);
            $row++;
        }

        if (count($laporan) > 0) {
            $sheet->mergeCells('A'.$row.':E'.$row);
            $sheet->setCellValue('A'.$row, 'Jumlah Total');
            $sheet->setCellValue('F'.$row, $totals['gaji_pokok']);
            $sheet->setCellValue('G'.$row, $totals['tunj_keluarga']);
            $sheet->setCellValue('H'.$row, $totals['tunj_struktural']);
            $sheet->setCellValue('I'.$row, $totals['tunj_fungsional']);
            $sheet->setCellValue('J'.$row, $totals['tunj_umum']);
            $sheet->setCellValue('K'.$row, $totals['tunj_pph']);
            $sheet->setCellValue('L'.$row, $totals['tunj_beras']);
            $sheet->setCellValue('M'.$row, $totals['tunj_lainnya']);
            $sheet->setCellValue('N'.$row, $totals['pembulatan']);
            $sheet->setCellValue('O'.$row, $totals['gaji_kotor']);
            $sheet->setCellValue('P'.$row, $totals['tunj_perbaikan']);
            $sheet->setCellValue('Q'.$row, '');
            $sheet->setCellValue('R'.$row, $totals['total_penghasilan']);
            $sheet->getStyle('A'.$row.':R'.$row)->getFont()->setBold(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:R'.($row - 1))->applyFromArray($styleArray);

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_SIKD_Rinci_'.str_replace(' ', '_', $periode).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
