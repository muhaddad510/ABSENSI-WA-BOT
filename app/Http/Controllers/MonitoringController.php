<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FonnteService;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * =====================================================
     * HALAMAN MONITORING ABSENSI
     * =====================================================
     */
    public function index(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $date        = $request->get('date', now()->toDateString());
        $status      = $request->get('status', 'all');
        $search      = $request->get('search', '');
        $classRoomId = $request->get('class_room_id');

        $user = Auth::user();

        $teacherId = $user->role === 'admin'
            ? $request->get('teacher_id', 'all')
            : $user->id;

        $teachers = $user->role === 'admin'
            ? User::where('role', 'dosen')->orderBy('name')->get()
            : collect();

        $classRooms = ClassRoom::query()
            ->when($user->role === 'dosen',
                fn ($q) => $q->where('teacher_id', $user->id)
            )
            ->when($user->role === 'admin' && $teacherId !== 'all',
                fn ($q) => $q->where('teacher_id', $teacherId)
            )
            ->orderBy('name')
            ->get();

        $attendances = Attendance::with(['student.classRoom'])
            ->whereDate('date', $date)
            ->when($user->role === 'dosen',
                fn ($q) => $q->whereHas('student',
                    fn ($s) => $s->where('teacher_id', $user->id)
                )
            )
            ->when($user->role === 'admin' && $teacherId !== 'all',
                fn ($q) => $q->whereHas('student',
                    fn ($s) => $s->where('teacher_id', $teacherId)
                )
            )
            ->when($classRoomId,
                fn ($q) => $q->whereHas('student',
                    fn ($s) => $s->where('class_room_id', $classRoomId)
                )
            )
            ->when($status !== 'all',
                fn ($q) => $q->where('status', $status)
            )
            ->when($search,
                fn ($q) => $q->whereHas('student', fn ($s) =>
                    $s->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                )
            )
            ->orderByRaw("
                CASE
                    WHEN status = 'belum_absen' THEN 1
                    WHEN status = 'absen_ditolak' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('check_in')
            ->paginate(10)
            ->withQueryString();

        $attendanceAlreadyOpened = false;
        $isActiveSession = false;

        if ($classRoomId) {
            $attendanceAlreadyOpened = Attendance::whereDate('date', $date)
                ->whereHas('student', fn ($q) =>
                    $q->where('class_room_id', $classRoomId)
                )
                ->exists();

            $isActiveSession = Attendance::whereDate('date', $date)
                ->whereIn('status', ['belum_absen', 'absen_ditolak'])
                ->whereHas('student', fn ($q) =>
                    $q->where('class_room_id', $classRoomId)
                )
                ->exists();
        }

        return view('monitoring.index', compact(
            'date',
            'status',
            'search',
            'classRoomId',
            'classRooms',
            'attendances',
            'teacherId',
            'teachers',
            'attendanceAlreadyOpened',
            'isActiveSession'
        ));
    }

    /**
     * =====================================================
     * MULAI ABSENSI
     * =====================================================
     */
    public function start(Request $request)
    {
        abort_if(Auth::user()->role !== 'dosen', 403);

        $request->validate([
            'date'          => ['required', 'date', 'before_or_equal:today'],
            'duration'      => ['required', 'in:1,2'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
        ]);

        $date        = $request->date;
        $duration    = (int) $request->duration;
        $classRoomId = $request->class_room_id;
        $teacherId   = Auth::id();

        $alreadyOpened = Attendance::whereDate('date', $date)
            ->whereHas('student', fn ($q) =>
                $q->where('class_room_id', $classRoomId)
                  ->where('teacher_id', $teacherId)
            )
            ->exists();

        if ($alreadyOpened) {
            return back()->with('error', 'Absensi kelas ini sudah pernah dibuka.');
        }

        $students = Student::where('teacher_id', $teacherId)
            ->where('class_room_id', $classRoomId)
            ->with('classRoom')
            ->get();

        foreach ($students as $student) {

            Attendance::create([
                'student_id'        => $student->id,
                'date'              => $date,
                'status'            => 'belum_absen',
                'attempt_count'     => 0,
                'final_notified_at' => null,
                'last_payload_hash' => null,
                'check_in'          => null,
                'lat'               => null,
                'lng'               => null,
                'distance_m'        => null,
            ]);

            if ($student->phone) {
                $endTime = Carbon::now()->addHours($duration)->format('H:i');

                FonnteService::send($student->phone,
"📢 ABSENSI DIBUKA

Halo {$student->name}
Kelas: *{$student->classRoom->name}*

📅 {$date}
⏰ Batas: {$endTime}

📍 HADIR → Kirim lokasi realtime
📝 IZIN → Ketik: IZIN
🤒 SAKIT → Ketik: SAKIT

⚠️ Tidak absen sampai batas waktu → *ALFA*");
            }
        }

        return back()->with('success', 'Absensi dimulai & notifikasi terkirim.');
    }

    /**
     * =====================================================
     * HENTIKAN ABSENSI (FINAL SYSTEM)
     * =====================================================
     */
    public function stop(Request $request)
    {
        abort_if(Auth::user()->role !== 'dosen', 403);

        $request->validate([
            'date'          => ['required', 'date', 'before_or_equal:today'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
        ]);

        Attendance::whereDate('date', $request->date)
            ->whereIn('status', ['belum_absen', 'absen_ditolak'])
            ->whereHas('student', fn ($q) =>
                $q->where('class_room_id', $request->class_room_id)
                  ->where('teacher_id', Auth::id())
            )
            ->update([
                'status'            => 'alfa',
                'note'              => 'Tidak absen sampai absensi ditutup dosen',
                'final_notified_at' => now(), // 🔒 BOT LOCK
            ]);

        return back()->with(
            'success',
            'Absensi ditutup. Status difinalkan menjadi ALFA.'
        );
    }

    /**
     * =====================================================
     * UPDATE STATUS MANUAL (OVERRIDE FINAL)
     * =====================================================
     */
    public function updateStatus(Request $request, Attendance $attendance)
    {
        if (
            Auth::user()->role !== 'admin' &&
            $attendance->student->teacher_id !== Auth::id()
        ) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:hadir,izin,sakit,alfa'],
        ]);

        $attendance->update([
            'status'            => $request->status,
            'final_notified_at' => null, // 🔓 buka bot
            'last_payload_hash' => null, // 🔁 reset dedup
            'check_in'          => $request->status === 'hadir'
                ? ($attendance->check_in ?? now()->format('H:i:s'))
                : null,
        ]);

        return back()->with(
            'success',
            'Status diperbarui. Bot akan mengirim notifikasi final baru.'
        );
    }
}
