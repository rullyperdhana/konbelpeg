<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tab === 'rekap' ? 'Rekapitulasi Master SKPD Induk' : 'Rincian Master Unit Kerja (UPT & Satker)' }}</title>
    <style>
        @page {
            margin: 12mm 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11px;
            font-weight: 600;
            margin: 3px 0 0 0;
            color: #334155;
        }
        .header p {
            font-size: 8.5px;
            color: #64748b;
            margin: 3px 0 0 0;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 8px;
            background: #f8fafc;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 5px 7px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .money {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        tr:nth-child(even) td {
            background-color: #fafbfd;
        }

        tfoot tr td {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            font-size: 7.5px;
            color: #64748b;
            text-align: right;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH PROVINSI</h1>
        <h2>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH (BPKAD)</h2>
        <p>{{ $tab === 'rekap' ? 'REKAPITULASI MASTER DATA SKPD INDUK (42 SKPD)' : ($tab === 'tree' ? 'STRUKTUR HIERARKI POHON MASTER UNIT KERJA (SKPD - UPT - SATKER)' : 'DAFTAR RINCIAN MASTER DATA UNIT KERJA (UPT & SATUAN KERJA)') }}</p>
    </div>

    <div class="meta-info">
        <div>
            @if($tab === 'rekap')
                <strong>Total SKPD Induk:</strong> {{ number_format(count($rekaps)) }} SKPD &bull;
                <strong>Total UPT/Sekolah:</strong> {{ number_format($rekaps->sum('total_upt')) }} Unit &bull;
                <strong>Total Pegawai:</strong> {{ number_format($rekaps->sum('total_pegawai')) }} Pegawai
            @elseif($tab === 'tree')
                <strong>Total SKPD Induk:</strong> {{ number_format(count($tree)) }} SKPD &bull;
                <strong>Total Unit Kerja:</strong> {{ number_format($totalUnitKerja) }} Unit
                @if(!empty($search))
                    &bull; <strong>Cari:</strong> "{{ $search }}"
                @endif
                &bull; <strong>Mode:</strong> Hierarki Pohon
            @else
                <strong>Total Unit Kerja:</strong> {{ number_format(isset($unitKerjas) ? $unitKerjas->count() : 0) }} Data &bull;
                <strong>Total SKPD Induk:</strong> {{ number_format($totalSkpdInduk) }} SKPD
                @if(!empty($skpdFilter))
                    &bull; <strong>SKPD:</strong> "{{ $skpdFilter }}"
                @endif
                @if(!empty($search))
                    &bull; <strong>Cari:</strong> "{{ $search }}"
                @endif
            @endif
        </div>
        <div style="text-align: right;">
            <strong>Tanggal Cetak:</strong> {{ date('d/m/Y H:i') }} WITA
        </div>
    </div>

    @if($tab === 'rekap')
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 25px;">NO</th>
                    <th>NAMA SKPD (INDUK)</th>
                    <th class="center" style="width: 90px;">JUMLAH UPT / CABANG</th>
                    <th class="center" style="width: 70px;">SATKER</th>
                    <th class="center" style="width: 80px;">TOTAL PEGAWAI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item->skpd }}</td>
                    <td class="center">{{ number_format($item->total_upt) }}</td>
                    <td class="center">{{ number_format($item->total_satker) }}</td>
                    <td class="center" style="font-weight: 600;">{{ number_format($item->total_pegawai) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="center" style="padding: 20px;">Data SKPD tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr>
                    <td colspan="2" class="center">TOTAL KESELURUHAN ({{ count($rekaps) }} SKPD INDUK)</td>
                    <td class="center">{{ number_format($rekaps->sum('total_upt')) }}</td>
                    <td class="center">{{ number_format($rekaps->sum('total_satker')) }}</td>
                    <td class="center">{{ number_format($rekaps->sum('total_pegawai')) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @elseif($tab === 'tree')
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">STRUKTUR HIERARKI UNIT KERJA</th>
                    <th style="width: 28%;">LEVEL HIERARKI</th>
                    <th class="center" style="width: 12%;">UPT / SATKER</th>
                    <th class="center" style="width: 10%;">PEGAWAI</th>
                </tr>
            </thead>
            <tbody>
                @php $noSkpd = 1; @endphp
                @forelse($tree as $skpdName => $skpdData)
                    <!-- SKPD Level 1 -->
                    <tr style="background-color: #e2e8f0; font-weight: bold;">
                        <td style="font-size: 9px; color: #0f172a; padding: 6px;">
                            {{ $noSkpd++ }}. {{ $skpdName }}
                        </td>
                        <td style="font-size: 8px; color: #475569;">SKPD INDUK</td>
                        <td class="center" style="font-size: 8px;">{{ number_format($skpdData['total_upt']) }} UPT</td>
                        <td class="center" style="font-size: 8.5px; color: #059669;">{{ number_format($skpdData['total_pegawai']) }}</td>
                    </tr>
                    
                    @foreach($skpdData['upts'] as $uptName => $uptData)
                        <!-- UPT Level 2 -->
                        <tr style="background-color: #f8fafc;">
                            <td style="padding-left: 20px; font-weight: 600; color: #1e293b;">
                                &bull; {{ $uptName }}
                            </td>
                            <td style="font-size: 7.5px; color: #64748b; padding-left: 20px;">UPT / CABANG</td>
                            <td class="center" style="font-size: 7.5px;">{{ count($uptData['items']) }} Satker</td>
                            <td class="center" style="font-size: 8px; font-weight: 600; color: #059669;">{{ number_format($uptData['total_pegawai']) }}</td>
                        </tr>

                        @foreach($uptData['items'] as $item)
                            @if($item->satker && $item->satker !== '-' && $item->satker !== $uptName)
                                <!-- Satker Level 3 (if distinct from UPT) -->
                                <tr>
                                    <td style="padding-left: 36px; color: #475569;">
                                        - {{ $item->satker }}
                                    </td>
                                    <td style="font-size: 7.5px; color: #94a3b8; padding-left: 36px;">SATUAN KERJA</td>
                                    <td class="center" style="font-size: 7.5px; color: #94a3b8;">-</td>
                                    <td class="center" style="font-size: 8px;">{{ number_format($item->pegawais_count) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" class="center" style="padding: 20px;">Data Unit Kerja tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 25px;">NO</th>
                    <th style="width: 38%;">SKPD (INDUK)</th>
                    <th style="width: 28%;">UPT / CABANG</th>
                    <th style="width: 24%;">SATUAN KERJA (SATKER)</th>
                    <th class="center" style="width: 50px;">PEGAWAI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unitKerjas as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item->skpd ?? '-' }}</td>
                    <td>{{ $item->upt ?? '-' }}</td>
                    <td>{{ $item->satker ?? '-' }}</td>
                    <td class="center">{{ number_format($item->pegawais_count ?? 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="center" style="padding: 20px;">Data Unit Kerja tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
            @if(isset($unitKerjas) && $unitKerjas->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="4" class="center">TOTAL SELURUH PEGAWAI TERDATA PADA UNIT KERJA</td>
                    <td class="center">{{ number_format($unitKerjas->sum('pegawais_count')) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @endif

    <div class="footer">
        Dokumen ini digenerate secara otomatis melalui Sistem Informasi Realisasi Belanja Pegawai (KONBELPEG) &bull; Dicetak pada: {{ date('d F Y, H:i:s') }} WITA
    </div>
</body>
</html>
