@extends('adminlte::page')

@section('title', 'Tambah Mahasiswa')

@section('content_header')
    <h1>Tambah Mahasiswa</h1>
@stop

@section('content')
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Mahasiswa (UI dulu)</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Backend simpan kita aktifkan nanti setelah UI fix ✅
            </div>

            <form>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" class="form-control" placeholder="Contoh: 20250123" required>
                </div>

                <div class="form-group">
                    <label>Semester</label>
                    <input type="number" class="form-control" min="1" max="14" placeholder="Contoh: 5" required>
                </div>

                <div class="form-group">
                    <label>No WhatsApp</label>
                    <input type="text" class="form-control" placeholder="Contoh: 628123456789" required>
                    <small class="text-muted">Format wajib pakai 62 (tanpa +)</small>
                </div>

                <hr>

                <button type="button" class="btn btn-success" disabled>
                    <i class="fas fa-save"></i> Simpan
                </button>

                <a href="{{ route('class-rooms.students', 1) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </form>
        </div>
    </div>
@stop
