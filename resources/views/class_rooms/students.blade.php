@extends('adminlte::page')

@section('title', 'Mahasiswa Kelas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Mahasiswa Kelas</h1>
            <small class="text-muted">Kelola mahasiswa berdasarkan kelas</small>
        </div>

        <div>
            <a href="{{ route('class-rooms.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <a href="{{ route('class-rooms.students.create', 1) }}" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Tambah Mahasiswa
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                Daftar Mahasiswa (UI dulu)
            </h3>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width:70px;">No</th>
                        <th>Nama</th>
                        <th style="width:140px;">NIM</th>
                        <th style="width:110px;">Semester</th>
                        <th style="width:180px;">No WA</th>
                        <th style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- dummy UI dulu --}}
                    <tr>
                        <td>1</td>
                        <td>Muhaddad</td>
                        <td>123</td>
                        <td>5</td>
                        <td>628123456789</td>
                        <td>
                            <button class="btn btn-sm btn-danger" disabled>
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            (Nanti datanya ngambil dari DB sesuai kelas)
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <a href="{{ route('class-rooms.attendance', 1) }}" class="btn btn-primary">
                <i class="fas fa-clipboard-check"></i> Mulai Absensi
            </a>
        </div>
    </div>
@stop
