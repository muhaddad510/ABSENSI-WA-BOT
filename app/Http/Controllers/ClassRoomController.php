<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassRoomController extends Controller
{
    // =====================
    // LIST KELAS
    // =====================
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $teacherId = $request->get(
            'teacher_id',
            Auth::user()->role === 'admin' ? 'all' : Auth::id()
        );

        $teachers = Auth::user()->role === 'admin'
            ? User::where('role', 'dosen')->orderBy('name')->get()
            : collect();

        $query = ClassRoom::with('teacher')

            ->when(Auth::user()->role !== 'admin', fn ($q) =>
                $q->where('teacher_id', Auth::id())
            )

            ->when(Auth::user()->role === 'admin' && $teacherId !== 'all', fn ($q) =>
                $q->where('teacher_id', $teacherId)
            )

            // 🔥 SEARCH UPGRADE (include semester)
            ->when($search, fn ($q) =>
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('course_name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%")
                       ->orWhere('semester', 'like', "%{$search}%")
                       ->orWhere('university', 'like', "%{$search}%");
                })
            );

        $classRooms = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('class_rooms.index', compact(
            'classRooms',
            'search',
            'teacherId',
            'teachers'
        ));
    }


    // =====================
    // FORM TAMBAH
    // =====================
    public function create()
    {
        return view('class_rooms.create');
    }


    // =====================
    // STORE
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'course_name' => ['required','string','max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_rooms', 'code')
                    ->where(fn ($q) => $q->where('teacher_id', Auth::id())),
            ],
            'semester' => ['required','string','max:10'], // 🔥 WAJIB
            'university' => ['nullable','string','max:255'],
        ], [
            'code.unique' => 'Kode kelas sudah dipakai di akun dosen ini.',
            'semester.required' => 'Semester wajib diisi.',
        ]);

        ClassRoom::create([
            'teacher_id' => Auth::id(),
            'name' => $request->name,
            'course_name' => $request->course_name,
            'code' => $request->code,
            'semester' => $request->semester, // 🔥 penting
            'university' => $request->university,
        ]);

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }


    // =====================
    // UPDATE
    // =====================
    public function update(Request $request, ClassRoom $classRoom)
    {
        if (
            Auth::user()->role !== 'admin' &&
            $classRoom->teacher_id !== Auth::id()
        ) {
            abort(403);
        }

        $request->validate([
            'name' => ['required','string','max:255'],
            'course_name' => ['required','string','max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_rooms', 'code')
                    ->where(fn ($q) => $q->where('teacher_id', $classRoom->teacher_id))
                    ->ignore($classRoom->id),
            ],
            'semester' => ['required','string','max:10'], // 🔥 WAJIB
            'university' => ['nullable','string','max:255'],
        ], [
            'code.unique' => 'Kode kelas sudah dipakai di akun dosen ini.',
        ]);

        $classRoom->update([
            'name' => $request->name,
            'course_name' => $request->course_name,
            'code' => $request->code,
            'semester' => $request->semester, // 🔥 penting
            'university' => $request->university,
        ]);

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }


    // =====================
    // DELETE
    // =====================
    public function destroy(ClassRoom $classRoom)
    {
        if (
            Auth::user()->role !== 'admin' &&
            $classRoom->teacher_id !== Auth::id()
        ) {
            abort(403);
        }

        $classRoom->delete();

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
