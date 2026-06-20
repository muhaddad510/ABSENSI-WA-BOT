<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Mahasiswa</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* ================= HEADER ================= */
        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border: none;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo {
            width: 85px;
        }

        .header-text {
            text-align: center;
        }

        .header-text h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-text p {
            margin: 4px 0 0 0;
            font-size: 11px;
        }

        hr {
            border: 0;
            border-top: 2px solid #000;
            margin: 8px 0 12px 0;
        }

        /* ================= INFO ================= */
        .info {
            font-size: 11px;
            margin-bottom: 10px;
        }

        /* ================= REKAP ================= */
        .rekap-table {
            width: 55%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .rekap-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .rekap-table .label {
            width: 70%;
        }

        .rekap-table .value {
            width: 30%;
            text-align: center;
            font-weight: bold;
        }

        /* ================= TABEL DATA ================= */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table th {
            background: #eaeaea;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td width="120">
                {{-- SESUAIKAN PATH LOGO --}}
                <img src="{{ public_path('images/logo-uniba.jpeg') }}" class="logo" alt="Logo Kampus">
            </td>
            <td class="header-text">
                <h1>LAPORAN ABSENSI MAHASISWA</h1>
                <p>
                    Periode
                    {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}
                    s/d
                    {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                </p>
            </td>
            <td width="120"></td>
        </tr>
    </table>
    <hr>
</div>

{{-- ================= INFO FILTER ================= --}}
<div class="info">
    <strong>Jumlah Data:</strong> {{ $data->count() }} Mahasiswa
</div>

{{-- ================= REKAP ================= --}}
<table class="rekap-table">
    <tr>
        <td class="label">Total Hadir</td>
        <td class="value">{{ $data->sum('total_hadir') }}</td>
    </tr>
    <tr>
        <td class="label">Total Izin</td>
        <td class="value">{{ $data->sum('total_izin') }}</td>
    </tr>
    <tr>
        <td class="label">Total Sakit</td>
        <td class="value">{{ $data->sum('total_sakit') }}</td>
    </tr>
    <tr>
        <td class="label">Total Alfa</td>
        <td class="value">{{ $data->sum('total_alfa') }}</td>
    </tr>
</table>

{{-- ================= TABEL DATA ================= --}}
<table class="data-table">
    <thead>
        <tr>
            <th width="30">No</th>
            <th width="110">NIM</th>
            <th>Nama Mahasiswa</th>
            <th width="90">Kelas</th>
            <th width="55">Hadir</th>
            <th width="55">Izin</th>
            <th width="55">Sakit</th>
            <th width="55">Alfa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $row->nim }}</td>
            <td>{{ $row->name }}</td>
            <td class="text-center">{{ $row->class_name ?? '-' }}</td>
            <td class="text-center">{{ $row->total_hadir }}</td>
            <td class="text-center">{{ $row->total_izin }}</td>
            <td class="text-center">{{ $row->total_sakit }}</td>
            <td class="text-center">{{ $row->total_alfa }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dicetak pada {{ now()->translatedFormat('d M Y H:i') }}
</div>

</body>
</html>
