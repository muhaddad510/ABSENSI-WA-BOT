@extends('layouts.adminlte')

@section('page_title', 'Dashboard Analytics')

@section('content')
<div class="container-fluid">

    {{-- FILTER --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="row align-items-end">

                    <div class="col-md-3">
                        <label class="small font-weight-bold">Tanggal Monitoring</label>
                        <input 
                            type="date"
                            name="date"
                            value="{{ $date }}"
                            max="{{ now()->toDateString() }}"
                            class="form-control shadow-sm"
                        >
                    </div>

                    @if(auth()->user()->role === 'admin')
                    <div class="col-md-4">
                        <label class="small font-weight-bold">Filter Dosen</label>
                        <select name="teacher_id" class="form-control shadow-sm select2">
                            <option value="all" {{ $teacherId=='all' ? 'selected' : '' }}>
                                Semua Dosen
                            </option>
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
                        <button class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt mr-1"></i> Perbarui Data
                        </button>

                        <a href="{{ route('dashboard') }}"
                           class="btn btn-default shadow-sm text-muted">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>


    {{-- STAT BOX --}}
    <div class="row">

        @php
            $hadir = $summary->hadir ?? 0;
            $izin  = $summary->izin ?? 0;
            $sakit = $summary->sakit ?? 0;
            $alfa  = $summary->alfa ?? 0;
        @endphp

        @php
            $stats = [
                ['label'=>'Total Mhs','val'=>$totalStudents,'bg'=>'bg-primary','icon'=>'fa-users'],
                ['label'=>'Hadir','val'=>$hadir,'bg'=>'bg-success','icon'=>'fa-check-circle'],
                ['label'=>'Belum','val'=>$belumAbsen,'bg'=>'bg-secondary','icon'=>'fa-clock'],
                ['label'=>'Izin','val'=>$izin,'bg'=>'bg-info','icon'=>'fa-envelope-open'],
                ['label'=>'Sakit','val'=>$sakit,'bg'=>'bg-warning','icon'=>'fa-medkit'],
                ['label'=>'Alfa','val'=>$alfa,'bg'=>'bg-danger','icon'=>'fa-times-circle'],
            ];
        @endphp

        @foreach($stats as $s)
        <div class="col-lg-2 col-6">
            <div class="small-box {{ $s['bg'] }} shadow-sm border-0">
                <div class="inner">
                    <h3 style="font-size:1.8rem">{{ $s['val'] }}</h3>
                    <p class="text-xs text-uppercase font-weight-bold">{{ $s['label'] }}</p>
                </div>
                <div class="icon">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
            </div>
        </div>
        @endforeach

    </div>



    {{-- LOG + CHART --}}
    <div class="row mt-2">

        {{-- LOG --}}
        <div class="col-md-8">
            <div class="card shadow-sm h-100">

                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-list-ul mr-2 text-primary"></i>
                        Log Absensi — {{ $logTitle }}
                    </h3>

                    @if($date !== now()->toDateString())
                        <span class="badge badge-warning">
                            Arsip
                        </span>
                    @else
                        <span class="badge badge-success">
                            Live
                        </span>
                    @endif

                </div>


                <div class="card-body p-0">
                    <div class="table-responsive">

                        <table class="table table-hover table-valign-middle mb-0">

                            <thead class="bg-light">
                                <tr class="text-muted small uppercase">
                                    <th>Mahasiswa</th>
                                    <th>Status</th>
                                    <th>Waktu Update</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($latestAttendances as $log)

                                @php
                                    $badge = [
                                        'hadir'=>'success',
                                        'alfa'=>'danger',
                                        'sakit'=>'warning',
                                        'izin'=>'info'
                                    ];
                                @endphp

                                <tr>
                                    <td class="font-weight-bold">
                                        {{ $log->student->name }}
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $badge[$log->status] ?? 'secondary' }}
                                            px-3 py-2 text-uppercase"
                                            style="letter-spacing:1px">
                                            {{ $log->status }}
                                        </span>
                                    </td>

                                    <td class="text-muted small">
                                        {{ $log->updated_at->format('H:i') }} WIB
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        Belum ada data absensi untuk tanggal ini.
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>



        {{-- CHART --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center">

                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-pie mr-2 text-primary"></i>
                        Rasio Kehadiran
                    </h3>
                </div>

                <div class="card-body d-flex flex-column justify-content-center">

                    <div style="position:relative;height:220px;">
                        <canvas id="attendanceChart"></canvas>
                    </div>

                    @php
                        $percent = $totalStudents > 0
                            ? round(($hadir / $totalStudents) * 100)
                            : 0;
                    @endphp

                    <div class="mt-4 text-left small">
                        <label class="mb-1 text-muted">
                            Tingkat Kehadiran: {{ $percent }}%
                        </label>

                        <div class="progress progress-xxs shadow-sm">
                            <div class="progress-bar bg-success"
                                 style="width: {{ $percent }}%">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection



@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const ctx = document.getElementById('attendanceChart');

    if(!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir','Izin','Sakit','Alfa','Belum'],
            datasets: [{
                data: [
                    {{ $hadir }},
                    {{ $izin }},
                    {{ $sakit }},
                    {{ $alfa }},
                    {{ $belumAbsen }}
                ],
                backgroundColor:[
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545',
                    '#adb5bd'
                ],
                borderWidth:0
            }]
        },
        options:{
            maintainAspectRatio:false,
            cutout:'75%',
            plugins:{
                legend:{
                    position:'bottom',
                    labels:{
                        usePointStyle:true,
                        padding:20,
                        font:{size:11}
                    }
                }
            }
        }
    });

});
</script>
@endpush
