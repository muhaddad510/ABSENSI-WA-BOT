@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Detail Absensi</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Nama:</strong> {{ $student->name }}</div>
            <div><strong>NIM:</strong> {{ $student->nim }}</div>
            <div><strong>Phone:</strong> {{ $student->phone }}</div>
            <div><strong>Tanggal:</strong> {{ $date }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if(!$attendance)
                <div class="alert alert-warning">Belum ada record absensi di tanggal ini.</div>
            @else
                <table class="table table-bordered">
                    <tr><th>Status</th><td>{{ $attendance->status }}</td></tr>
                    <tr><th>Check-in</th><td>{{ $attendance->check_in ?? '-' }}</td></tr>
                    <tr><th>Check-out</th><td>{{ $attendance->check_out ?? '-' }}</td></tr>
                    <tr><th>Lat</th><td>{{ $attendance->lat ?? '-' }}</td></tr>
                    <tr><th>Lng</th><td>{{ $attendance->lng ?? '-' }}</td></tr>
                    <tr><th>Distance (m)</th><td>{{ $attendance->distance_m ?? '-' }}</td></tr>
                    <tr><th>Note</th><td>{{ $attendance->note ?? '-' }}</td></tr>
                </table>
            @endif

            <a href="{{ route('monitoring.index', ['date' => $date]) }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
