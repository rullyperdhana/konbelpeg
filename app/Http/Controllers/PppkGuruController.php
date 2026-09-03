<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PppkGuruController extends Controller
{
    private function getReportData($periode)
    {
        $pegawais = Pegawai::with(['realisasiGajis' => function ($q) use ($periode) {
            $q->where('periode', $periode);
        }])
            ->where('status_pegawai', 'PPPK')
            ->where('jenis_pegawai', 'GURU')
            ->get()
            ->groupBy('golru');

        $laporan = [];
        $totals = [
            'jumlah' => 0, 'gaji_pokok' => 0, 'tunj_keluarga' => 0, 'tunj_struktural' => 0,
            'tunj_fungsional' => 0, 'tunj_umum' => 0, 'tunj_beras' => 0,
            'tunj_lainnya' => 0, 'pembulatan' => 0, 'gaji_kotor' => 0, 'tunj_perbaikan' => 0,
            'total_penghasilan' => 0,
        ];

        foreach ($pegawais as $golongan => $pegawaiGroup) {
            $row = [
                'golongan' => $golongan ?: 'Tanpa Golongan',
                'jumlah' => 0,
                'gaji_pokok' => 0,
                'tunj_keluarga' => 0,
                'tunj_struktural' => 0,
                'tunj_fungsional' => 0,
                'tunj_umum' => 0,
                'tunj_beras' => 0,
                'tunj_lainnya' => 0,
                'pembulatan' => 0,
                'gaji_kotor' => 0,
                'tunj_perbaikan' => 0,
                'total_penghasilan' => 0,
            ];

            foreach ($pegawaiGroup as $peg) {
                $gaji = $peg->realisasiGajis->first();

                if ($gaji && $gaji->raw_data) {
                    $row['jumlah'] += 1;

                    $raw = is_string($gaji->raw_data) ? json_decode($gaji->raw_data, true) : $gaji->raw_data;

                    if (is_array($raw)) {
                        $tunj_keluarga = (float) ($raw['tjistri'] ?? 0) + (float) ($raw['tjanak'] ?? 0);
                        $tunj_struktural = (float) ($raw['tjstruk'] ?? 0) + (float) ($raw['tjeselon'] ?? 0);
                        $tunj_fungsional = (float) ($raw['tjfungsi'] ?? 0);
                        $tunj_umum = (float) ($raw['tjumum'] ?? 0);
                        $tunj_beras = (float) ($raw['tjberas'] ?? 0);
                        $tunj_lainnya = (float) ($raw['tjkk'] ?? 0) + (float) ($raw['tjkm'] ?? 0) +
                                        (float) ($raw['tjkhusus'] ?? 0) + (float) ($raw['tjterpenci'] ?? 0) +
                                        (float) ($raw['tjaskes'] ?? 0);
                        $pembulatan = (float) ($raw['tbulat'] ?? 0);
                        $gaji_pokok = (float) ($raw['gapok'] ?? 0);

                        // Menghitung Gaji Kotor TANPA PPH
                        $gajiKotor = $gaji_pokok + $tunj_keluarga + $tunj_struktural +
                                     $tunj_fungsional + $tunj_umum +
                                     $tunj_beras + $tunj_lainnya + $pembulatan;

                        $tunj_perbaikan = (float) ($raw['tjtpp'] ?? 0);
                        $total_penghasilan = $gajiKotor + $tunj_perbaikan;

                        $row['gaji_pokok'] += $gaji_pokok;
                        $row['tunj_keluarga'] += $tunj_keluarga;
                        $row['tunj_struktural'] += $tunj_struktural;
                        $row['tunj_fungsional'] += $tunj_fungsional;
                        $row['tunj_umum'] += $tunj_umum;
                        $row['tunj_beras'] += $tunj_beras;
                        $row['tunj_lainnya'] += $tunj_lainnya;
                        $row['pembulatan'] += $pembulatan;
                        $row['gaji_kotor'] += $gajiKotor;
                        $row['tunj_perbaikan'] += $tunj_perbaikan;
                        $row['total_penghasilan'] += $total_penghasilan;
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

        usort($laporan, function ($a, $b) {
            return strnatcmp($a['golongan'], $b['golongan']);
        });

        return ['laporan' => $laporan, 'totals' => $totals];
    }

    public function index(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);

        return view('laporan.pppk-guru.index', [
            'laporan' => $data['laporan'],
            'totals' => $data['totals'],
            'periode' => $periode,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);

        $html = view('laporan.pppk-guru.pdf', [
            'laporan' => $data['laporan'],
            'totals' => $data['totals'],
            'periode' => $periode,
        ])->render();

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Laporan_PPPK_GURU_'.str_replace(' ', '_', $periode).'.pdf';

        return $dompdf->stream($fileName, ['Attachment' => true]);
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $data = $this->getReportData($periode);
        $laporan = $data['laporan'];
        $totals = $data['totals'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'DAFTAR RINCIAN JUMLAH DAN REALISASI PEMBAYARAN GAJI GURU Gaji Induk PPPK + TPP');
        $sheet->mergeCells('A1:P1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->setCellValue('A2', 'Nama Daerah : Provinsi Kalimantan Selatan');
        $sheet->mergeCells('A2:P2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'BULAN '.explode(' ', $periode)[0]);
        $sheet->mergeCells('A3:P3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'TAHUN '.explode(' ', $periode)[1]);
        $sheet->mergeCells('A4:P4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Table
        $startRow = 6;
        $sheet->mergeCells('A'.$startRow.':A'.($startRow + 1));
        $sheet->setCellValue('A'.$startRow, 'No');
        $sheet->mergeCells('B'.$startRow.':B'.($startRow + 1));
        $sheet->setCellValue('B'.$startRow, 'Golongan');
        $sheet->mergeCells('C'.$startRow.':C'.($startRow + 1));
        $sheet->setCellValue('C'.$startRow, "Jumlah GURU\nPPPK Yang\nDiangkat");
        $sheet->mergeCells('D'.$startRow.':D'.($startRow + 1));
        $sheet->setCellValue('D'.$startRow, "Jumlah GURU\nPPPK Yang\nTelah Diangkat");
        $sheet->mergeCells('E'.$startRow.':E'.($startRow + 1));
        $sheet->setCellValue('E'.$startRow, "Gaji\nPokok");
        $sheet->mergeCells('F'.$startRow.':F'.($startRow + 1));
        $sheet->setCellValue('F'.$startRow, "Tunjangan\nKeluarga");
        $sheet->mergeCells('G'.$startRow.':H'.$startRow);
        $sheet->setCellValue('G'.$startRow, 'Tunjangan Jabatan');
        $sheet->setCellValue('G'.($startRow + 1), 'Struktural');
        $sheet->setCellValue('H'.($startRow + 1), 'Fungsional');
        $sheet->mergeCells('I'.$startRow.':I'.($startRow + 1));
        $sheet->setCellValue('I'.$startRow, "Tunjangan\nUmum");
        $sheet->mergeCells('J'.$startRow.':J'.($startRow + 1));
        $sheet->setCellValue('J'.$startRow, "Tunjangan\nBeras");
        $sheet->mergeCells('K'.$startRow.':K'.($startRow + 1));
        $sheet->setCellValue('K'.$startRow, "Tunjangan\nLainnya");
        $sheet->mergeCells('L'.$startRow.':L'.($startRow + 1));
        $sheet->setCellValue('L'.$startRow, "Lain-lain\nPembulatan");
        $sheet->mergeCells('M'.$startRow.':M'.($startRow + 1));
        $sheet->setCellValue('M'.$startRow, "Gaji\nKotor");
        $sheet->mergeCells('N'.$startRow.':N'.($startRow + 1));
        $sheet->setCellValue('N'.$startRow, "Tunjangan\nPerbaikan\nPenghasilan\n(TPP)/ Tunjangan");
        $sheet->mergeCells('O'.$startRow.':O'.($startRow + 1));
        $sheet->setCellValue('O'.$startRow, "Kode\nBayar");
        $sheet->mergeCells('P'.$startRow.':P'.($startRow + 1));
        $sheet->setCellValue('P'.$startRow, "Total\nPenghasilan");

        $numRow = $startRow + 2;
        $sheet->setCellValue('A'.$numRow, '(1)');
        $sheet->setCellValue('B'.$numRow, '(2)');
        $sheet->setCellValue('C'.$numRow, '(3)');
        $sheet->setCellValue('D'.$numRow, '(4)');
        $sheet->setCellValue('E'.$numRow, '(5)');
        $sheet->setCellValue('F'.$numRow, '(6)');
        $sheet->setCellValue('G'.$numRow, '(7)');
        $sheet->setCellValue('H'.$numRow, '(8)');
        $sheet->setCellValue('I'.$numRow, '(9)');
        $sheet->setCellValue('J'.$numRow, '(10)');
        $sheet->setCellValue('K'.$numRow, '(11)');
        $sheet->setCellValue('L'.$numRow, '(12)');
        $sheet->setCellValue('M'.$numRow, '(13)=(5)+..+(12)');
        $sheet->setCellValue('N'.$numRow, '(14)');
        $sheet->setCellValue('O'.$numRow, '(15)');
        $sheet->setCellValue('P'.$numRow, '(16)=(13)+(14)');

        $sheet->getStyle('A'.$startRow.':P'.$numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$startRow.':P'.$numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$startRow.':P'.$numRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A'.$startRow.':P'.$numRow)->getFont()->setBold(true);

        $row = $numRow + 1;
        $no = 1;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $item['golongan']);
            $sheet->setCellValue('C'.$row, $item['jumlah']); // Kolom 3
            $sheet->setCellValue('D'.$row, $item['jumlah']); // Kolom 4 sama dengan kolom 3
            $sheet->setCellValue('E'.$row, $item['gaji_pokok']);
            $sheet->setCellValue('F'.$row, $item['tunj_keluarga']);
            $sheet->setCellValue('G'.$row, $item['tunj_struktural']);
            $sheet->setCellValue('H'.$row, $item['tunj_fungsional']);
            $sheet->setCellValue('I'.$row, $item['tunj_umum']);
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
            $sheet->setCellValue('D'.$row, $totals['jumlah']);
            $sheet->setCellValue('E'.$row, $totals['gaji_pokok']);
            $sheet->setCellValue('F'.$row, $totals['tunj_keluarga']);
            $sheet->setCellValue('G'.$row, $totals['tunj_struktural']);
            $sheet->setCellValue('H'.$row, $totals['tunj_fungsional']);
            $sheet->setCellValue('I'.$row, $totals['tunj_umum']);
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
        $sheet->getStyle('A'.$startRow.':P'.($row - 1))->applyFromArray($styleArray);

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_PPPK_GURU_'.str_replace(' ', '_', $periode).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    private function getReportDataRinci($periode, $skpdFilter, $search = null, $golonganFilter = null)
    {
        $query = Pegawai::with(['unitKerja', 'realisasiGajis' => function ($q) use ($periode) {
            $q->where('periode', $periode);
        }])
            ->where('status_pegawai', 'like', 'PPPK%')
            ->where('jenis_pegawai', 'GURU');

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
            'tunj_fungsional' => 0, 'tunj_umum' => 0, 'tunj_beras' => 0,
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
                    $tunj_beras = (float) ($raw['tjberas'] ?? 0);
                    $tunj_lainnya = (float) ($raw['tjkk'] ?? 0) + (float) ($raw['tjkm'] ?? 0) +
                                    (float) ($raw['tjkhusus'] ?? 0) + (float) ($raw['tjterpenci'] ?? 0) +
                                    (float) ($raw['tjaskes'] ?? 0);
                    $pembulatan = (float) ($raw['tbulat'] ?? 0);
                    $gaji_pokok = (float) ($raw['gapok'] ?? 0);

                    $gajiKotor = $gaji_pokok + $tunj_keluarga + $tunj_struktural +
                                 $tunj_fungsional + $tunj_umum +
                                 $tunj_beras + $tunj_lainnya + $pembulatan;

                    $tunj_perbaikan = (float) ($raw['tjtpp'] ?? 0);
                    $total_penghasilan = $gajiKotor + $tunj_perbaikan;

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
                        'tunj_beras' => $tunj_beras,
                        'tunj_lainnya' => $tunj_lainnya,
                        'pembulatan' => $pembulatan,
                        'gaji_kotor' => $gajiKotor,
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
        $golonganList = Pegawai::where('status_pegawai', 'like', 'PPPK%')->where('jenis_pegawai', 'GURU')->select('golru')->distinct()->whereNotNull('golru')->orderBy('golru')->pluck('golru');

        $data = $this->getReportDataRinci($periode, $skpdFilter, $search, $golonganFilter);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 100;
        $laporanCollection = collect($data['laporan']);
        $items = $laporanCollection->slice(($page - 1) * $perPage, $perPage)->all();
        $paginatedLaporan = new LengthAwarePaginator($items, $laporanCollection->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);

        return view('laporan.pppk-guru.rinci', [
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
        $sheet->setCellValue('K1', "Tunjangan\nBeras");
        $sheet->mergeCells('L1:L2');
        $sheet->setCellValue('L1', "Tunjangan\nLainnya");
        $sheet->mergeCells('M1:M2');
        $sheet->setCellValue('M1', "Lain-lain\nPembulatan");
        $sheet->mergeCells('N1:N2');
        $sheet->setCellValue('N1', "Gaji\nKotor");
        $sheet->mergeCells('O1:O2');
        $sheet->setCellValue('O1', "Tunjangan\nPerbaikan\nPenghasilan");
        $sheet->mergeCells('P1:P2');
        $sheet->setCellValue('P1', "Kode\nBayar");
        $sheet->mergeCells('Q1:Q2');
        $sheet->setCellValue('Q1', "Total\nPenghasilan");

        $sheet->getStyle('A1:Q2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:Q2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:Q2')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:Q2')->getFont()->setBold(true);

        $row = 3;
        $no = 1;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValueExplicit('B'.$row, (string) $item['nip'], DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $item['nama']);
            $sheet->setCellValue('D'.$row, $item['skpd']);
            $sheet->setCellValue('E'.$row, $item['golongan']);
            $sheet->setCellValue('F'.$row, $item['gaji_pokok']);
            $sheet->setCellValue('G'.$row, $item['tunj_keluarga']);
            $sheet->setCellValue('H'.$row, $item['tunj_struktural']);
            $sheet->setCellValue('I'.$row, $item['tunj_fungsional']);
            $sheet->setCellValue('J'.$row, $item['tunj_umum']);
            $sheet->setCellValue('K'.$row, $item['tunj_beras']);
            $sheet->setCellValue('L'.$row, $item['tunj_lainnya']);
            $sheet->setCellValue('M'.$row, $item['pembulatan']);
            $sheet->setCellValue('N'.$row, $item['gaji_kotor']);
            $sheet->setCellValue('O'.$row, $item['tunj_perbaikan']);
            $sheet->setCellValue('P'.$row, '-');
            $sheet->setCellValue('Q'.$row, $item['total_penghasilan']);
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
            $sheet->setCellValue('K'.$row, $totals['tunj_beras']);
            $sheet->setCellValue('L'.$row, $totals['tunj_lainnya']);
            $sheet->setCellValue('M'.$row, $totals['pembulatan']);
            $sheet->setCellValue('N'.$row, $totals['gaji_kotor']);
            $sheet->setCellValue('O'.$row, $totals['tunj_perbaikan']);
            $sheet->setCellValue('P'.$row, '');
            $sheet->setCellValue('Q'.$row, $totals['total_penghasilan']);
            $sheet->getStyle('A'.$row.':Q'.$row)->getFont()->setBold(true);
            $row++;
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:Q'.($row - 1))->applyFromArray($styleArray);

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_PPPK_GURU_Rinci_'.str_replace(' ', '_', $periode).'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdfRinci(Request $request)
    {
        $periode = $request->input('periode', 'Juli 2026');
        $skpdFilter = $request->input('skpd');
        $search = $request->input('search');
        $golonganFilter = $request->input('golongan');

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $data = $this->getReportDataRinci($periode, $skpdFilter, $search, $golonganFilter);

        $html = view('laporan.pppk-guru.pdf_rinci', [
            'laporan' => $data['laporan'],
            'totals' => $data['totals'],
            'periode' => $periode,
        ])->render();

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Laporan_PPPK_GURU_Rinci_'.str_replace(' ', '_', $periode).'.pdf';

        return $dompdf->stream($fileName, ['Attachment' => true]);
    }
}
