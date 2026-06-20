@extends('layouts.adminlte')

@section('page_title', 'Tambah Mahasiswa')

@section('content')
<div class="row">
    <div class="col-md-10 col-lg-8">

        <div class="card shadow-sm">

            <div class="card-header bg-primary">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-plus mr-2"></i>
                    Form Tambah Mahasiswa
                </h3>
            </div>

            <form method="POST" action="{{ route('students.store') }}">
                @csrf

                <div class="card-body">

                    {{-- NAMA --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Mahasiswa</label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Contoh: Budi Santoso"
                               required>

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIM --}}
                    <div class="form-group">
                        <label class="font-weight-bold">NIM</label>

                        <input type="text"
                               name="nim"
                               value="{{ old('nim') }}"
                               class="form-control @error('nim') is-invalid @enderror"
                               placeholder="Contoh: 20250123"
                               required>

                        @error('nim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 🔥 AUTO SEMESTER --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Semester</label>

                        <input type="text"
                               id="semester_display"
                               class="form-control bg-light"
                               placeholder="Pilih kelas terlebih dahulu"
                               readonly>

                        <small class="text-muted">
                            Semester mengikuti kelas yang dipilih
                        </small>
                    </div>

                    {{-- WHATSAPP --}}
                    <div class="form-group">
                        <label class="font-weight-bold">
                            No. WhatsApp
                            <small class="text-muted">(format 628xxxx)</small>
                        </label>

                        <input type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="6281234567890"
                               required>

                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- KELAS --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Kelas</label>

                        <select name="class_room_id"
                                id="class_selector"
                                class="form-control @error('class_room_id') is-invalid @enderror"
                                required>

                            <option value="">-- Pilih Kelas --</option>

                            @foreach($classRooms as $c)
                                <option value="{{ $c->id }}"
                                        data-semester="{{ $c->semester }}"
                                    {{ old('class_room_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('class_room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">

                    <a href="{{ route('students.index') }}"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali
                    </a>

                    <button type="submit"
                            class="btn btn-success font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Mahasiswa
                    </button>

                </div>

            </form>
        </div>

    </div>
</div>
@endsection


@push('js')
<script>

// AUTO ISI SEMESTER SAAT PILIH KELAS
document.getElementById('class_selector')
.addEventListener('change', function(){

let semester =
this.options[this.selectedIndex]
.getAttribute('data-semester');

document.getElementById('semester_display')
.value = semester
? "Semester " + semester
: "-";

});

</script>
@endpush
