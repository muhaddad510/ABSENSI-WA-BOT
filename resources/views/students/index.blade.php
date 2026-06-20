@extends('layouts.adminlte')

@section('page_title', 'Data Mahasiswa')

@section('content')
<div class="row">
<div class="col-12">

{{-- TOOLBAR --}}
<div class="d-flex justify-content-end mb-3">

    <a href="{{ route('students.template.download') }}"
       class="btn btn-success btn-sm mr-2">
        <i class="fas fa-download"></i> Template
    </a>

    <button class="btn btn-info btn-sm mr-2"
            data-toggle="modal"
            data-target="#importModal">
        <i class="fas fa-file-upload"></i> Import CSV
    </button>

    <a href="{{ route('students.create') }}"
       class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Tambah Mahasiswa
    </a>

</div>

{{-- FILTER --}}
<div class="card mb-3">
<div class="card-body">
<form method="GET" action="{{ route('students.index') }}">

<div class="row">

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

<div class="col-md-4">
<label>Search</label>
<input type="text"
name="search"
value="{{ $search }}"
class="form-control"
placeholder="Nama / NIM / WhatsApp / Semester / Kelas">
</div>

<div class="col-md-2 d-flex align-items-end">
<button class="btn btn-primary btn-block">
<i class="fas fa-filter"></i> Filter
</button>
</div>

</div>
</form>
</div>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead class="bg-light">
<tr>
<th width="60">No</th>
<th>Nama</th>
<th>NIM</th>
<th width="130">Semester</th>
<th>WhatsApp</th>
<th>Kelas</th>

@if(auth()->user()->role === 'admin')
<th>Dosen</th>
@endif

<th width="120" class="text-center">Aksi</th>
</tr>
</thead>

<tbody>

@forelse($students as $i => $s)
<tr>

<td>{{ $students->firstItem() + $i }}</td>

<td class="font-weight-bold">
{{ $s->name }}
</td>

<td>{{ $s->nim }}</td>

{{-- SEMESTER FULL DARI CLASS --}}
<td>
@if($s->classRoom?->semester)
<span class="badge badge-primary">
Semester {{ $s->classRoom->semester }}
</span>
@else
<span class="text-muted">-</span>
@endif
</td>

<td>{{ $s->phone }}</td>

<td>{{ $s->classRoom->name ?? '-' }}</td>

@if(auth()->user()->role === 'admin')
<td>{{ $s->teacher->name ?? '-' }}</td>
@endif

<td class="text-center">

<button class="btn btn-warning btn-sm editBtn"
data-id="{{ $s->id }}"
data-name="{{ $s->name }}"
data-nim="{{ $s->nim }}"
data-phone="{{ $s->phone }}"
data-class="{{ $s->class_room_id }}"
data-semester="{{ $s->classRoom->semester ?? '-' }}"
data-toggle="modal"
data-target="#editModal">
<i class="fas fa-edit"></i>
</button>

<form action="{{ route('students.destroy', $s->id) }}"
method="POST"
class="d-inline"
onsubmit="return confirm('Yakin hapus mahasiswa ini?')">
@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
<i class="fas fa-trash"></i>
</button>

</form>

</td>
</tr>

@empty
<tr>
<td colspan="8" class="text-center text-muted py-4">
Tidak ada data mahasiswa
</td>
</tr>
@endforelse

</tbody>
</table>

{{ $students->links() }}

</div>
</div>

</div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="editModal">
<div class="modal-dialog modal-dialog-centered">

<form method="POST"
id="editForm"
class="modal-content">

@csrf
@method('PUT')

<div class="modal-header bg-warning">
<h5 class="modal-title font-weight-bold">
Edit Mahasiswa
</h5>

<button type="button"
class="close"
data-dismiss="modal">
&times;
</button>
</div>

<div class="modal-body">

<div class="form-group">
<label>Nama</label>
<input type="text"
name="name"
id="edit_name"
class="form-control"
required>
</div>

<div class="form-group">
<label>NIM</label>
<input type="text"
name="nim"
id="edit_nim"
class="form-control"
required>
</div>

{{-- AUTO SEMESTER --}}
<div class="form-group">
<label>Semester</label>
<input type="text"
id="edit_semester"
class="form-control bg-light"
readonly>

<small class="text-muted">
Semester mengikuti kelas
</small>
</div>

<div class="form-group">
<label>WhatsApp</label>
<input type="text"
name="phone"
id="edit_phone"
class="form-control"
required>
</div>

<div class="form-group">
<label>Kelas</label>
<select name="class_room_id"
id="edit_class"
class="form-control"
required>

@foreach($classRooms as $c)
<option value="{{ $c->id }}"
data-semester="{{ $c->semester }}">
{{ $c->name }}
</option>
@endforeach

</select>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-secondary"
data-dismiss="modal">
Batal
</button>

<button class="btn btn-warning">
Simpan
</button>
</div>

</form>
</div>
</div>

@include('students._import_modal')

@endsection


@push('js')
<script>

document.addEventListener('DOMContentLoaded', function(){

    const editForm = document.getElementById('editForm');
    const editName = document.getElementById('edit_name');
    const editNim = document.getElementById('edit_nim');
    const editPhone = document.getElementById('edit_phone');
    const editClass = document.getElementById('edit_class');
    const editSemester = document.getElementById('edit_semester');

    // OPEN MODAL
    document.querySelectorAll('.editBtn').forEach(button => {

        button.addEventListener('click', function(){

            editForm.action = `/students/${this.dataset.id}`;

            editName.value = this.dataset.name;
            editNim.value = this.dataset.nim;
            editPhone.value = this.dataset.phone;
            editClass.value = this.dataset.class;

            editSemester.value = this.dataset.semester || '-';
        });

    });

    // CHANGE CLASS → AUTO SEMESTER
    editClass.addEventListener('change', function(){

        const semester =
            this.options[this.selectedIndex]
            .getAttribute('data-semester');

        editSemester.value = semester || '-';
    });

});

</script>
@endpush
