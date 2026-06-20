@extends('layouts.adminlte')

@section('page_title', 'Tambah Kelas')

@section('content')
<div class="row">
    <div class="col-lg-7 col-md-9">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-primary">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-1"></i>
                    Tambah Kelas Baru
                </h3>
            </div>

            <form action="{{ route('class-rooms.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    {{-- Nama --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Kelas</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Contoh: TI-3A"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mata Kuliah --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Mata Kuliah</label>
                        <input type="text"
                               name="course_name"
                               value="{{ old('course_name') }}"
                               class="form-control @error('course_name') is-invalid @enderror"
                               placeholder="Contoh: Pemrograman Web"
                               required>
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kode --}}
                    <div class="form-group">
                        <label class="font-weight-bold">
                            Kode Kelas
                            <small class="text-muted">(harus unik)</small>
                        </label>
                        <input type="text"
                               name="code"
                               value="{{ old('code') }}"
                               class="form-control @error('code') is-invalid @enderror"
                               placeholder="Contoh: WEB-TI3A-2026"
                               required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 🔥 SEMESTER --}}
                    <div class="form-group">
                        <label class="font-weight-bold">
                            Semester
                        </label>
                        <input type="text"
                               name="semester"
                               value="{{ old('semester') }}"
                               class="form-control @error('semester') is-invalid @enderror"
                               placeholder="Contoh: 2 / 4 / 6"
                               required>

                        @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kampus --}}
                    <div class="form-group">
                        <label class="font-weight-bold">
                            Universitas / Kampus
                        </label>
                        <input type="text"
                               name="university"
                               value="{{ old('university') }}"
                               class="form-control @error('university') is-invalid @enderror"
                               placeholder="Contoh: Universitas Mataram">

                        @error('university')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">

                    <a href="{{ route('class-rooms.index') }}"
                       class="btn btn-light border">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali
                    </a>

                    <button class="btn btn-success px-4">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Kelas
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
@endsection
