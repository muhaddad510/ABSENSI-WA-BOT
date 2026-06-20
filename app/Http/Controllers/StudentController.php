<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // =====================
    // LIST MAHASISWA
    // =====================
    public function index(Request $request)
    {
        $search = $request->get('search');
        $classRoomId = $request->get('class_room_id', 'all');

        $teacherId = $request->get(
            'teacher_id',
            Auth::user()->role === 'admin' ? 'all' : Auth::id()
        );

        $teachers = Auth::user()->role === 'admin'
            ? User::where('role', 'dosen')->orderBy('name')->get()
            : collect();

        $classRooms = ClassRoom::query()
            ->when(Auth::user()->role !== 'admin', fn ($q) =>
                $q->where('teacher_id', Auth::id())
            )
            ->when(Auth::user()->role === 'admin' && $teacherId !== 'all', fn ($q) =>
                $q->where('teacher_id', $teacherId)
            )
            ->orderBy('name')
            ->get();

        $students = Student::with(['classRoom', 'teacher'])

            ->when(Auth::user()->role !== 'admin', fn ($q) =>
                $q->where('teacher_id', Auth::id())
            )
            ->when(Auth::user()->role === 'admin' && $teacherId !== 'all', fn ($q) =>
                $q->where('teacher_id', $teacherId)
            )

            ->when($classRoomId !== 'all', fn ($q) =>
                $q->where('class_room_id', $classRoomId)
            )

            // 🔥 ADVANCED SEARCH
            ->when($search, function ($q) use ($search) {

                $q->where(function ($qq) use ($search) {

                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")

                        ->orWhereHas('classRoom', function ($qr) use ($search) {
                            $qr->where('semester', 'like', "%{$search}%")
                               ->orWhere('name', 'like', "%{$search}%");
                        });

                });

            })

            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact(
            'students',
            'search',
            'classRoomId',
            'classRooms',
            'teacherId',
            'teachers'
        ));
    }


    // =====================
    // FORM TAMBAH
    // =====================
    public function create()
    {
        $classRooms = ClassRoom::where('teacher_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('students.create', compact('classRooms'));
    }


    // =====================
    // SIMPAN MAHASISWA
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nim' => 'required|string|max:50|unique:students,nim',
            'phone' => 'required|string|max:30',
            'class_room_id' => 'required|exists:class_rooms,id',
        ]);

        $phone = $this->normalizePhone($request->phone);

        if (!$phone) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp tidak valid'
            ])->withInput();
        }

        if (Student::where('phone', $phone)->exists()) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp sudah digunakan'
            ])->withInput();
        }

        Student::create([
            'teacher_id' => Auth::id(),
            'class_room_id' => $request->class_room_id,
            'name' => $request->name,
            'nim' => $request->nim,
            'phone' => $phone,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }


    // =====================
    // UPDATE MAHASISWA
    // =====================
    public function update(Request $request, Student $student)
    {
        $this->authorizeStudent($student);

        $request->validate([
            'name' => 'required|string|max:255',
            'nim' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nim')->ignore($student->id),
            ],
            'phone' => 'required|string|max:30',
            'class_room_id' => 'required|exists:class_rooms,id',
        ]);

        $phone = $this->normalizePhone($request->phone);

        if (
            Student::where('phone', $phone)
                ->where('id', '!=', $student->id)
                ->exists()
        ) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp sudah digunakan'
            ])->withInput();
        }

        $student->update([
            'name' => $request->name,
            'nim' => $request->nim,
            'phone' => $phone,
            'class_room_id' => $request->class_room_id,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }


    // =====================
    // HAPUS MAHASISWA
    // =====================
    public function destroy(Student $student)
    {
        $this->authorizeStudent($student);

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Mahasiswa berhasil dihapus.');
    }


    // =====================
    // DOWNLOAD TEMPLATE
    // =====================
    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_import_mahasiswa_SISTEM_INFORMASI_UNIBA.csv');

        abort_if(!file_exists($path), 404, 'Template tidak ditemukan');

        return response()->download($path);
    }


    // =====================
    // IMPORT CSV 🔥 FINAL DESIGN
    // =====================
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:text/plain,text/csv|max:2048',
            'class_room_id' => 'required|exists:class_rooms,id',
        ]);

        $classRoomId = $request->class_room_id;

        $rows = array_map(function ($line) {
            return str_contains($line, ';')
                ? str_getcsv($line, ';')
                : str_getcsv($line, ',');
        }, file($request->file('file')->getRealPath()));

        unset($rows[0]); // skip header

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {

            if (count($row) < 3) {
                $skipped++;
                continue;
            }

            $nim      = trim($row[0] ?? '');
            $name     = trim($row[1] ?? '');
            $phoneRaw = trim($row[2] ?? '');

            if (!$nim || !$name || !$phoneRaw) {
                $skipped++;
                continue;
            }

            $phone = $this->normalizePhone($phoneRaw);

            if (!$phone) {
                $skipped++;
                continue;
            }

            if (
                Student::where('nim', $nim)->exists() ||
                Student::where('phone', $phone)->exists()
            ) {
                $skipped++;
                continue;
            }

            Student::create([
                'teacher_id' => Auth::id(),
                'class_room_id' => $classRoomId,
                'nim' => $nim,
                'name' => $name,
                'phone' => $phone,
            ]);

            $imported++;
        }

        return redirect()->route('students.index')->with(
            'success',
            "Import selesai. Berhasil: {$imported}, Dilewati: {$skipped}"
        );
    }


    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 9) return null;

        if (str_starts_with($phone, '08')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            return '62' . $phone;
        }

        return $phone;
    }


    private function authorizeStudent(Student $student): void
    {
        if (
            Auth::user()->role !== 'admin' &&
            $student->teacher_id !== Auth::id()
        ) {
            abort(403);
        }
    }
}
