<?php

namespace App\Http\Controllers;

use App\Exports\AttendancesExport;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * =====================================================
     * HALAMAN LAPORAN / REKAP ABSENSI
     * =====================================================
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        /* ===============================
         * FILTER DASAR
         * =============================== */
        $dateFrom    = $request->date_from ?? now()->toDateString();
        $dateTo      = $request->date_to   ?? now()->toDateString();
        $classRoomId = $request->class_room_id ?? 'all';

        // legacy (tidak dipakai, dijaga agar view lama aman)
        $status = $request->status ?? 'all';

        /* ===============================
         * FILTER DOSEN
         * =============================== */
        $teacherId = $user->role === 'admin'
            ? ($request->teacher_id ?? 'all')
            : $user->id;

        $teachers = $user->role === 'admin'
            ? User::where('role', 'dosen')->orderBy('name')->get()
            : collect();

        /* ===============================
         * DROPDOWN KELAS
         * =============================== */
        $classRoomsQuery = ClassRoom::orderBy('name');

        if ($user->role !== 'admin') {
            $classRoomsQuery->where('teacher_id', $user->id);
        } elseif ($teacherId !== 'all') {
            $classRoomsQuery->where('teacher_id', $teacherId);
        }

        $classRooms = $classRoomsQuery->get();

        /* ===============================
         * QUERY REKAP ABSENSI (FINAL)
         * =============================== */
        $attendances = Student::query()
            ->leftJoin('attendances', function ($join) use ($dateFrom, $dateTo) {
                $join->on('attendances.student_id', '=', 'students.id')
                     ->whereBetween('attendances.date', [$dateFrom, $dateTo])
                     // 🔒 HANYA STATUS FINAL
                     ->whereIn('attendances.status', ['hadir','izin','sakit','alfa']);
            })
            ->select(
                'students.id',
                'students.name',
                'students.nim',
                'students.class_room_id',

                DB::raw("SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir"),
                DB::raw("SUM(CASE WHEN attendances.status = 'izin'  THEN 1 ELSE 0 END) AS total_izin"),
                DB::raw("SUM(CASE WHEN attendances.status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit"),
                DB::raw("SUM(CASE WHEN attendances.status = 'alfa'  THEN 1 ELSE 0 END) AS total_alfa")
            );

        // filter dosen
        if ($user->role !== 'admin') {
            $attendances->where('students.teacher_id', $user->id);
        } elseif ($teacherId !== 'all') {
            $attendances->where('students.teacher_id', $teacherId);
        }

        // filter kelas
        if ($classRoomId !== 'all') {
            $attendances->where('students.class_room_id', $classRoomId);
        }

        $attendances = $attendances
            ->groupBy(
                'students.id',
                'students.name',
                'students.nim',
                'students.class_room_id'
            )
            ->orderBy('students.name')
            ->paginate(15)
            ->withQueryString();

        return view('reports.index', compact(
            'dateFrom',
            'dateTo',
            'classRoomId',
            'status',
            'classRooms',
            'attendances',
            'teacherId',
            'teachers'
        ));
    }

    /**
     * =====================================================
     * EXPORT EXCEL
     * =====================================================
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        $dateFrom    = $request->date_from ?? now()->toDateString();
        $dateTo      = $request->date_to   ?? now()->toDateString();
        $classRoomId = $request->class_room_id ?? 'all';
        $status      = $request->status ?? 'all';

        $teacherId = $user->role === 'admin'
            ? ($request->teacher_id ?? 'all')
            : $user->id;

        $className = 'SEMUA_KELAS';
        if ($classRoomId !== 'all') {
            $class = ClassRoom::find($classRoomId);
            if ($class) {
                $className = strtoupper(str_replace(' ', '_', $class->name));
            }
        }

        $filename = "laporan-{$className}-{$dateFrom}_{$dateTo}.xlsx";

        return Excel::download(
            new AttendancesExport(
                $user->role,
                $user->id,
                $teacherId,
                $dateFrom,
                $dateTo,
                $classRoomId,
                $status
            ),
            $filename
        );
    }

    /**
     * =====================================================
     * EXPORT PDF
     * =====================================================
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        $dateFrom    = $request->date_from ?? now()->toDateString();
        $dateTo      = $request->date_to   ?? now()->toDateString();
        $classRoomId = $request->class_room_id ?? 'all';

        $teacherId = $user->role === 'admin'
            ? ($request->teacher_id ?? 'all')
            : $user->id;

        $data = Student::query()
            ->leftJoin('attendances', function ($join) use ($dateFrom, $dateTo) {
                $join->on('attendances.student_id', '=', 'students.id')
                     ->whereBetween('attendances.date', [$dateFrom, $dateTo])
                     // 🔒 HANYA STATUS FINAL
                     ->whereIn('attendances.status', ['hadir','izin','sakit','alfa']);
            })
            ->leftJoin('class_rooms', 'class_rooms.id', '=', 'students.class_room_id')
            ->select(
                'students.name',
                'students.nim',
                'class_rooms.name as class_name',

                DB::raw("SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir"),
                DB::raw("SUM(CASE WHEN attendances.status = 'izin'  THEN 1 ELSE 0 END) AS total_izin"),
                DB::raw("SUM(CASE WHEN attendances.status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit"),
                DB::raw("SUM(CASE WHEN attendances.status = 'alfa'  THEN 1 ELSE 0 END) AS total_alfa")
            );

        if ($user->role !== 'admin') {
            $data->where('students.teacher_id', $user->id);
        } elseif ($teacherId !== 'all') {
            $data->where('students.teacher_id', $teacherId);
        }

        if ($classRoomId !== 'all') {
            $data->where('students.class_room_id', $classRoomId);
        }

        $data = $data
            ->groupBy(
                'students.id',
                'students.name',
                'students.nim',
                'class_rooms.name'
            )
            ->orderBy('students.name')
            ->get();

        $className = 'SEMUA_KELAS';
        if ($classRoomId !== 'all') {
            $class = ClassRoom::find($classRoomId);
            if ($class) {
                $className = strtoupper(str_replace(' ', '_', $class->name));
            }
        }

        $filename = "laporan-{$className}-{$dateFrom}_{$dateTo}.pdf";

        $pdf = Pdf::loadView('reports.pdf', [
            'data'     => $data,
            'dateFrom'=> $dateFrom,
            'dateTo'  => $dateTo,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
