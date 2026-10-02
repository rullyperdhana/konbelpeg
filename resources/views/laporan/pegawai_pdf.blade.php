<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daftar Pegawai per SKPD / UPT / Satker</title>
    <style>
        @page {
            margin: 10mm 10mm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
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
            margin: 2px 0 0 0;
            color: #334155;
        }
        .header p {
            font-size: 8.5px;
            color: #64748b;
            margin: 2px 0 0 0;
            font-weight: bold;
        }

        .meta-info {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8px;
            background: #f8fafc;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            border: none;
            padding: 2px 4px;
            font-size: 8px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 7.5px;
            text-align: left;
            text-transform: uppercase;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #fafbfd;
        }

        tfoot tr td {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }

        .badge-status {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .footer {
            margin-top: 10px;
            font-size: 7.5px;
            color: #64748b;
            text-align: right;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH PROVINSI</h1>
        <h2>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH (BPKAD)</h2>
        <p>LAPORAN DAFTAR PEGAWAI MENURUT SKPD, UPT, DAN SATUAN KERJA</p>
    </div>

    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td style="width: 70%;">
                    <strong>SKPD Induk:</strong> {{ $skpd ?: 'Semua SKPD (42 SKPD)' }} &bull;
                    <strong>UPT / Cabang:</strong> {{ $upt ?: 'Semua UPT' }} &bull;
                    <strong>Satker:</strong> {{ $satker ?: 'Semua Satker' }}<br>
                    <strong>Status Pegawai:</strong> {{ $status ?: 'Semua Status (PNS / PPPK)' }} &bull;
                    <strong>Jenis Pegawai:</strong> {{ $jenis ?: 'Semua Jenis' }}
                    @if(!empty($search))
                        &bull; <strong>Pencarian:</strong> "{{ $search }}"
                    @endif
                </td>
                <td class="right" style="width: 30%; vertical-align: top;">
                    <strong>Total Data:</strong> {{ number_format($totalPegawai) }} Pegawai<br>
                    <strong>Tanggal Cetak:</strong> {{ date('d/m/Y H:i') }} WITA
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width: 24px;">NO</th>
                <th class="center" style="width: 105px;">NIP</th>
                <th style="width: 140px;">NAMA PEGAWAI</th>
                <th class="center" style="width: 45px;">GOLRU</th>
                <th>JABATAN</th>
                <th class="center" style="width: 55px;">JENIS</th>
                <th class="center" style="width: 55px;">STATUS</th>
                <th style="width: 120px;">SKPD (INDUK)</th>
                <th style="width: 110px;">UPT / CABANG</th>
                <th style="width: 110px;">SATUAN KERJA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawais as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center" style="font-family: monospace; font-size: 7.5px;">{{ $item->nip }}</td>
                <td style="font-weight: 600;">{{ $item->nama }}</td>
                <td class="center">{{ $item->golru ?? '-' }}</td>
                <td>{{ $item->jabatan?->nama ?? '-' }}</td>
                <td class="center">{{ $item->jenis_pegawai ?? '-' }}</td>
                <td class="center">
                    <span class="badge-status">{{ $item->status_pegawai ?? '-' }}</span>
                </td>
                <td style="font-size: 7.5px;">{{ $item->unitKerja?->skpd ?? '-' }}</td>
                <td style="font-size: 7.5px;">{{ $item->unitKerja?->upt ?? '-' }}</td>
                <td style="font-size: 7.5px;">{{ $item->unitKerja?->satker ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="center" style="padding: 20px;">Data Pegawai tidak ditemukan untuk kriteria filter ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pegawais) > 0)
        <tfoot>
            <tr>
                <td colspan="6" class="center">TOTAL PEGAWAI DALAM LAPORAN INI</td>
                <td colspan="4" class="center">{{ number_format(count($pegawais)) }} Orang @if($totalPegawai > count($pegawais)) (dari total {{ number_format($totalPegawai) }} data) @endif</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Dokumen dicetak melalui Sistem Informasi Realisasi Belanja Pegawai (KONBELPEG) &bull; Dicetak pada: {{ date('d F Y, H:i:s') }} WITA
    </div>
</body>
</html>
