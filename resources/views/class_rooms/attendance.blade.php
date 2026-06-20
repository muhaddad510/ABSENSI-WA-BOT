@extends('adminlte::page')

@section('title', 'Absensi Kelas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Absensi Kelas</h1>
            <small class="text-muted">Pilih status kehadiran mahasiswa</small>
        </div>

        <a href="{{ route('class-rooms.students', 1) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Absensi (UI dulu)</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-warning">
                Ini masih UI. Nanti statusnya disimpan + bisa auto kirim WA.
            </div>

            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width:70px;">No</th>
                        <th>Nama</th>
                        <th style="width:140px;">NIM</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:360px;">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- dummy UI --}}
                    <tr>
                        <td>1</td>
                        <td>Muhaddad</td>
                        <td>123</td>
                        <td><span class="badge badge-secondary">Belum Dipilih</span></td>
                        <td>
                            <button class="btn btn-sm btn-success" disabled>
                                <i class="fas fa-check"></i> Hadir
                            </button>
                            <button class="btn btn-sm btn-warning" disabled>
                                <i class="fas fa-procedures"></i> Sakit
                            </button>
                            <button class="btn btn-sm btn-info" disabled>
                                <i class="fas fa-envelope"></i> Izin
                            </button>
                            <button class="btn btn-sm btn-danger" disabled>
                                <i class="fas fa-times"></i> Alpha
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            (Nanti ini looping dari mahasiswa per kelas)
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <button class="btn btn-primary" disabled>
                <i class="fas fa-paper-plane"></i> Kirim Rekap ke WhatsApp
            </button>
        </div>
    </div>
@stop
