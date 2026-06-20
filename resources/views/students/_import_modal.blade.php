<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">

        <form method="POST"
              action="{{ route('students.import') }}"
              enctype="multipart/form-data"
              class="modal-content border-0 shadow-lg">

            @csrf

            {{-- HEADER --}}
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-file-csv mr-2"></i>
                    Import Mahasiswa
                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">
                    &times;
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">

                {{-- INFO BOX --}}
                <div class="alert alert-info shadow-sm">

                    <strong>Format CSV wajib:</strong>

                    <div class="mt-2 p-2 bg-white border rounded font-monospace small">
                        NIM,Nama,No_HP
                    </div>

                    <small class="text-muted">
                        ✔ Gunakan pemisah koma (,)<br>
                        ✔ Semester mengikuti kelas<br>
                        ✔ Mahasiswa akan langsung masuk ke kelas yang dipilih
                    </small>
                </div>


                {{-- 🔥 PILIH KELAS --}}
                <div class="form-group">

                    <label class="font-weight-bold">
                        Pilih Kelas Tujuan
                    </label>

                    <select name="class_room_id"
                            id="classSelect"
                            class="form-control"
                            required>

                        <option value="">
                            -- Pilih Kelas --
                        </option>

                        @foreach($classRooms as $c)
                            <option value="{{ $c->id }}"
                                    data-semester="{{ $c->semester }}">
                                {{ $c->name }}
                                @if($c->semester)
                                    — Semester {{ $c->semester }}
                                @endif
                            </option>
                        @endforeach

                    </select>

                    <small class="text-muted">
                        Semua mahasiswa akan dimasukkan ke kelas ini
                    </small>

                </div>


                {{-- 🔥 AUTO SEMESTER --}}
                <div class="form-group">

                    <label class="font-weight-bold">
                        Semester
                    </label>

                    <input type="text"
                           id="semesterPreview"
                           class="form-control bg-light"
                           readonly
                           placeholder="Mengikuti kelas">

                </div>



                {{-- FILE UPLOAD --}}
                <div class="form-group">

                    <label class="font-weight-bold">
                        Pilih File CSV
                    </label>

                    <div class="custom-file">
                        <input type="file"
                               name="file"
                               class="custom-file-input"
                               id="csvFile"
                               accept=".csv"
                               required>

                        <label class="custom-file-label" for="csvFile">
                            Pilih file CSV...
                        </label>
                    </div>

                    <small class="text-muted">
                        Maksimal 2MB
                    </small>

                </div>

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0">

                <button type="button"
                        class="btn btn-light"
                        data-dismiss="modal">
                    Batal
                </button>

                <button id="importBtn"
                        class="btn btn-info font-weight-bold px-4"
                        disabled>
                    <i class="fas fa-upload mr-1"></i>
                    Import Sekarang
                </button>

            </div>

        </form>
    </div>
</div>


@push('js')
<script>

document.addEventListener("DOMContentLoaded", function () {

    const fileInput = document.querySelector('#csvFile');
    const classSelect = document.querySelector('#classSelect');
    const semesterPreview = document.querySelector('#semesterPreview');
    const importBtn = document.querySelector('#importBtn');


    // tampilkan nama file
    fileInput.addEventListener('change', function(e){

        let fileName = e.target.files[0]?.name ?? "Pilih file CSV...";
        e.target.nextElementSibling.innerText = fileName;

        toggleImportButton();
    });


    // auto semester
    classSelect.addEventListener('change', function(){

        let semester =
            this.options[this.selectedIndex]
            .getAttribute('data-semester');

        semesterPreview.value = semester
            ? "Semester " + semester
            : "-";

        toggleImportButton();
    });


    // disable tombol kalau belum lengkap
    function toggleImportButton(){

        if(classSelect.value && fileInput.files.length > 0){
            importBtn.disabled = false;
        }else{
            importBtn.disabled = true;
        }

    }

});
</script>
@endpush
