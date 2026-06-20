<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $teacherId = $request->get(
            'teacher_id',
            Auth::user()->role === 'admin' ? 'all' : Auth::id()
        );

        // =============================
        // LIST DOSEN (ADMIN ONLY)
        // =============================
        $teachers = Auth::user()->role === 'admin'
            ? User::where('role', 'dosen')->orderBy('name')->get()
            : collect();

        // =============================
        // DYNAMIC TITLE
        // =============================
        $selectedDate = Carbon::parse($date);

        $logTitle = $selectedDate->isToday()
            ? 'Hari Ini'
            : $selectedDate->translatedFormat('d F Y');

        // =============================
        // TOTAL MAHASISWA
        // =============================
        $studentsQuery = Student::query();

        if (Auth::user()->role !== 'admin') {
            $studentsQuery->where('teacher_id', Auth::id());
        } elseif ($teacherId !== 'all') {
            $studentsQuery->where('teacher_id', $teacherId);
        }

        $totalStudents = $studentsQuery->count();

        // =============================
        // QUERY ABSENSI
        // =============================
        $attQuery = Attendance::query()
            ->whereDate('date', $date);

        if (Auth::user()->role !== 'admin') {
            $attQuery->whereHas('student', fn($q) =>
                $q->where('teacher_id', Auth::id())
            );
        } elseif ($teacherId !== 'all') {
            $attQuery->whereHas('student', fn($q) =>
                $q->where('teacher_id', $teacherId)
            );
        }

        // =============================
        // SUMMARY STATUS (FAST)
        // =============================
        $summary = (clone $attQuery)
            ->selectRaw("
                COALESCE(SUM(status = 'hadir'),0) as hadir,
                COALESCE(SUM(status = 'izin'),0) as izin,
                COALESCE(SUM(status = 'sakit'),0) as sakit,
                COALESCE(SUM(status = 'alfa'),0) as alfa
            ")
            ->first();

        $sudahAbsen =
            $summary->hadir +
            $summary->izin +
            $summary->sakit +
            $summary->alfa;

        $belumAbsen = max(0, $totalStudents - $sudahAbsen);

        // =============================
        // LOG TERBARU
        // =============================
        $latestAttendances = (clone $attQuery)
            ->with('student')
            ->latest('updated_at')
            ->limit(7)
            ->get();

        return view('dashboard', compact(
            'date',
            'teacherId',
            'teachers',
            'totalStudents',
            'summary',
            'belumAbsen',
            'latestAttendances',
            'logTitle'
        ));
    }
}
