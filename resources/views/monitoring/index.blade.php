@extends('layouts.adminlte')

@section('page_title', 'Monitoring Absensi')

@section('content')
<div class="row">
    <div class="col-12">

        {{-- ================= FILTER ================= --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('monitoring.index') }}">
                    <div class="row align-items-end">

                        {{-- Tanggal --}}
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Tanggal</label>
                            <input type="date"
                                   name="date"
                                   value="{{ $date ?? now()->toDateString() }}"
                                   max="{{ now()->toDateString() }}"
                                   class="form-control"
                                   required>
                        </div>

                        {{-- Dosen (Admin) --}}
                        @if(auth()->user()->role === 'admin')
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Dosen</label>
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

                        {{-- Kelas --}}
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Kelas</label>
                            <select name="class_room_id" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classRooms as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (string)$classRoomId === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="all">Semua Status</option>
                                <option value="belum_absen" {{ $status=='belum_absen'?'selected':'' }}>Menunggu</option>
                                <option value="hadir" {{ $status=='hadir'?'selected':'' }}>Hadir</option>
                                <option value="izin" {{ $status=='izin'?'selected':'' }}>Izin</option>
                                <option value="sakit" {{ $status=='sakit'?'selected':'' }}>Sakit</option>
                                <option value="alfa" {{ $status=='alfa'?'selected':'' }}>Alfa</option>
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="col-md-4 mt-3">
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="Nama / NIM / Phone">
                        </div>

                        {{-- Button --}}
                        <div class="col-md-8 mt-3">
                            <button class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('monitoring.index') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ================= CONTROL ABSENSI DOSEN ================= --}}
                @if(auth()->user()->role === 'dosen' && $classRoomId)
                <div class="mt-3">
                    @if($attendanceAlreadyOpened && !$isActiveSession)
                        <span class="badge badge-secondary px-3 py-2">
                            <i class="fas fa-lock"></i> Absensi Ditutup
                        </span>
                    @elseif($isActiveSession)
                        <form method="POST"
                              action="{{ route('monitoring.stop') }}"
                              class="d-inline"
                              onsubmit="return confirm('Hentikan absensi sekarang?')">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="class_room_id" value="{{ $classRoomId }}">
                            <button class="btn btn-danger btn-sm">
                                <i class="fas fa-stop"></i> Hentikan Absensi
                            </button>
                        </form>
                    @else
                        <form method="POST"
                              action="{{ route('monitoring.start') }}"
                              class="d-inline"
                              onsubmit="return confirm('Mulai absensi sekarang?')">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="class_room_id" value="{{ $classRoomId }}">
                            <select name="duration"
                                    class="form-control form-control-sm d-inline-block w-auto mr-2">
                                <option value="1">1 Jam</option>
                                <option value="2">2 Jam</option>
                            </select>
                            <button class="btn btn-success btn-sm">
                                <i class="fas fa-play"></i> Mulai Absensi
                            </button>
                        </form>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card shadow-sm">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jarak</th>
                            <th>Lokasi</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $i => $a)
                        @php
                            $statusMap = [
                                'belum_absen' => ['label' => 'Menunggu', 'badge' => 'secondary'],
                                'hadir'       => ['label' => 'Hadir',    'badge' => 'success'],
                                'izin'        => ['label' => 'Izin',     'badge' => 'info'],
                                'sakit'       => ['label' => 'Sakit',    'badge' => 'warning'],
                                'alfa'        => ['label' => 'Alfa',     'badge' => 'danger'],
                            ];
                            $s = $statusMap[$a->status] ?? ['label'=>'-', 'badge'=>'dark'];
                        @endphp
                        <tr>
                            <td>{{ $attendances->firstItem() + $i }}</td>
                            <td class="font-weight-bold">{{ $a->student->name }}</td>
                            <td>{{ $a->student->nim }}</td>
                            <td>
                                <span class="badge badge-{{ $s['badge'] }}">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td>{{ $a->check_in ?? '-' }}</td>
                            <td>{{ $a->distance_m ? $a->distance_m.' m' : '-' }}</td>
                            <td>
                                @if($a->lat && $a->lng)
                                    <a href="https://maps.google.com/?q={{ $a->lat }},{{ $a->lng }}" target="_blank">
                                        Maps
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editModal{{ $a->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL --}}
                        <div class="modal fade" id="editModal{{ $a->id }}">
                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                <form method="POST"
                                      action="{{ route('monitoring.updateStatus',$a->id) }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title font-weight-bold">
                                                Edit Absensi {{ $a->student->name }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Jam Masuk</label>
                                                <input type="time"
                                                       name="check_in"
                                                       value="{{ $a->check_in }}"
                                                       class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="hadir">Hadir</option>
                                                    <option value="izin">Izin</option>
                                                    <option value="sakit">Sakit</option>
                                                    <option value="alfa">Alfa</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button class="btn btn-warning">Simpan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
