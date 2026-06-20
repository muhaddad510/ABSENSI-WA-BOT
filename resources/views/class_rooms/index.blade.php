@extends('layouts.adminlte')

@section('page_title', 'Data Kelas')

@section('content')
<div class="row">
    <div class="col-12">

        {{-- HEADER ACTION --}}
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('class-rooms.create') }}"
               class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Kelas
            </a>
        </div>

        {{-- FILTER --}}
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('class-rooms.index') }}">
                    <div class="row">

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

                        <div class="{{ auth()->user()->role === 'admin' ? 'col-md-5' : 'col-md-7' }}">
                            <label class="small font-weight-bold">Search</label>
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="Nama kelas / Mata kuliah / Kode / Semester / Universitas">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-block">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('class-rooms.index') }}"
                               class="btn btn-secondary btn-block">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">

                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Kelas</th>
                            <th>Mata Kuliah</th>
                            <th width="120">Semester</th>
                            <th width="120">Kode</th>
                            <th>Universitas</th>

                            @if(auth()->user()->role === 'admin')
                                <th>Dosen</th>
                            @endif

                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($classRooms as $i => $c)
                        <tr>
                            <td>{{ $classRooms->firstItem() + $i }}</td>

                            <td class="font-weight-bold">{{ $c->name }}</td>
                            <td>{{ $c->course_name }}</td>

                            <td>
                                <span class="badge badge-primary px-3 py-2">
                                    Semester {{ $c->semester }}
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-info px-3 py-2">
                                    {{ $c->code }}
                                </span>
                            </td>

                            <td>{{ $c->university ?? '-' }}</td>

                            @if(auth()->user()->role === 'admin')
                                <td>{{ $c->teacher->name ?? '-' }}</td>
                            @endif

                            <td class="text-center">
                                {{-- EDIT --}}
                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editModal{{ $c->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- DELETE --}}
                                <form action="{{ route('class-rooms.destroy', $c->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div class="modal fade" id="editModal{{ $c->id }}">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">

                                    <form method="POST"
                                          action="{{ route('class-rooms.update', $c->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title font-weight-bold">
                                                Edit Kelas
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                &times;
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nama Kelas</label>
                                                <input type="text" name="name"
                                                       class="form-control"
                                                       value="{{ $c->name }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Mata Kuliah</label>
                                                <input type="text" name="course_name"
                                                       class="form-control"
                                                       value="{{ $c->course_name }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Semester</label>
                                                <input type="text" name="semester"
                                                       class="form-control"
                                                       value="{{ $c->semester }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Kode</label>
                                                <input type="text" name="code"
                                                       class="form-control"
                                                       value="{{ $c->code }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Universitas / Kampus</label>
                                                <input type="text" name="university"
                                                       class="form-control"
                                                       value="{{ $c->university }}">
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-dismiss="modal">
                                                Batal
                                            </button>
                                            <button class="btn btn-warning">
                                                Simpan
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 8 : 7 }}"
                                class="text-center text-muted py-4">
                                Belum ada data kelas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $classRooms->links() }}
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
