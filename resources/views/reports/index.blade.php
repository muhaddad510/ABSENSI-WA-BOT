@extends('layouts.adminlte')

@section('page_title', 'Laporan Absensi')

@section('content')
<div class="row">
    <div class="col-12">

        {{-- =====================================================
        FILTER LAPORAN
        ====================================================== --}}
        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filter Laporan
                </h3>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}">
                    <div class="row align-items-end">

                        <div class="col-md-3">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from"
                                   value="{{ $dateFrom }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to"
                                   value="{{ $dateTo }}"
                                   class="form-control">
                        </div>

                        @if(auth()->user()->role === 'admin')
                        <div class="col-md-3">
                            <label>Dosen</label>
                            <select name="teacher_id" class="form-control">
                                <option value="all">Semua Dosen</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (string)$teacherId === (string)$t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <label>Kelas</label>
                            <select name="class_room_id" class="form-control">
                                <option value="all">Semua Kelas</option>
                                @foreach($classRooms as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (string)$classRoomId === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BUTTON --}}
                        <div class="col-md-3 mt-3">
                            <button class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                        </div>

                        <div class="col-md-3 mt-3">
                            <a href="{{ route('reports.index') }}"
                               class="btn btn-secondary btn-block">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>

                        {{-- EXPORT --}}
                        @php
                            $hasData = $attendances->total() > 0;
                            $kelasText = $classRoomId === 'all'
                                ? 'Semua Kelas'
                                : optional($classRooms->firstWhere('id',$classRoomId))->name;
                        @endphp

                        <div class="col-md-3 mt-3">
                            <a href="{{ $hasData ? route('reports.exportExcel', request()->query()) : '#' }}"
                               class="btn btn-success btn-block {{ !$hasData ? 'disabled' : '' }}">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>

                        <div class="col-md-3 mt-3">
                            <a href="{{ $hasData ? route('reports.exportPdf', request()->query()) : '#' }}"
                               class="btn btn-danger btn-block {{ !$hasData ? 'disabled' : '' }}">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                        </div>

                    </div>
                </form>

                <small class="text-muted d-block mt-2">
                    Export untuk:
                    <strong>{{ $kelasText }}</strong> |
                    Periode:
                    <strong>{{ $dateFrom }}</strong> s/d <strong>{{ $dateTo }}</strong>
                </small>
            </div>
        </div>

        {{-- =====================================================
        SUMMARY (DASHBOARD STYLE)
        ====================================================== --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Hadir</span>
                        <span class="info-box-number">{{ $attendances->sum('total_hadir') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Izin</span>
                        <span class="info-box-number">{{ $attendances->sum('total_izin') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-notes-medical"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Sakit</span>
                        <span class="info-box-number">{{ $attendances->sum('total_sakit') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Alfa</span>
                        <span class="info-box-number">{{ $attendances->sum('total_alfa') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
        TABLE
        ====================================================== --}}
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-table mr-1"></i> Rekapitulasi Absensi Mahasiswa
                </h3>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover text-center mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="60">No</th>
                            <th class="text-left">Nama Mahasiswa</th>
                            <th width="160">NIM</th>
                            <th width="90">Hadir</th>
                            <th width="90">Izin</th>
                            <th width="90">Sakit</th>
                            <th width="90">Alfa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $i => $a)
                        <tr>
                            <td>{{ $attendances->firstItem() + $i }}</td>
                            <td class="text-left font-weight-bold">{{ $a->name }}</td>
                            <td>{{ $a->nim }}</td>
                            <td><span class="badge badge-success px-3">{{ $a->total_hadir }}</span></td>
                            <td><span class="badge badge-info px-3">{{ $a->total_izin }}</span></td>
                            <td><span class="badge badge-warning px-3">{{ $a->total_sakit }}</span></td>
                            <td><span class="badge badge-danger px-3">{{ $a->total_alfa }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4">
                                Tidak ada data untuk filter yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer clearfix">
                {{ $attendances->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
