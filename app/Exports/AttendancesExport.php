<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendancesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected string $role;
    protected int $userId;
    protected string|int $teacherId;
    protected string $dateFrom;
    protected string $dateTo;
    protected string|int $classRoomId;
    protected string $status;

    public function __construct(
        string $role,
        int $userId,
        string|int $teacherId,
        string $dateFrom,
        string $dateTo,
        string|int $classRoomId,
        string $status
    ) {
        $this->role        = $role;
        $this->userId      = $userId;
        $this->teacherId   = $teacherId;
        $this->dateFrom    = $dateFrom;
        $this->dateTo      = $dateTo;
        $this->classRoomId = $classRoomId;
        $this->status      = $status;
    }

    /**
     * =====================================================
     * DATA EXCEL
     * =====================================================
     */
    public function collection()
    {
        $query = Student::query()
            ->leftJoin('class_rooms', 'class_rooms.id', '=', 'students.class_room_id')
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.student_id', '=', 'students.id')
                     ->whereBetween('attendances.date', [$this->dateFrom, $this->dateTo]);
            })
            ->select(
                'students.id',
                'students.nim',
                'students.name',
                'class_rooms.name as class_name',

                DB::raw("SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir"),
                DB::raw("SUM(CASE WHEN attendances.status = 'izin'  THEN 1 ELSE 0 END) AS total_izin"),
                DB::raw("SUM(CASE WHEN attendances.status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit"),
                DB::raw("SUM(CASE WHEN attendances.status = 'alfa'  THEN 1 ELSE 0 END) AS total_alfa")
            );

        /* ================= FILTER DOSEN ================= */
        if ($this->role !== 'admin') {
            $query->where('students.teacher_id', $this->userId);
        } elseif ($this->teacherId !== 'all') {
            $query->where('students.teacher_id', $this->teacherId);
        }

        /* ================= FILTER KELAS ================= */
        if ($this->classRoomId !== 'all') {
            $query->where('students.class_room_id', $this->classRoomId);
        }

        $data = $query
            ->groupBy(
                'students.id',
                'students.nim',
                'students.name',
                'class_rooms.name'
            )
            ->orderBy('students.name')
            ->get();

        /* ================= FORMAT ROW ================= */
        return $data->map(function ($row, $index) {
            return [
                $index + 1,
                $row->nim,
                $row->name,
                $row->class_name ?? '-',
                (int) $row->total_hadir,
                (int) $row->total_izin,
                (int) $row->total_sakit,
                (int) $row->total_alfa,
            ];
        });
    }

    /**
     * =====================================================
     * HEADER EXCEL
     * =====================================================
     */
    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Kelas',
            'Hadir',
            'Izin',
            'Sakit',
            'Alfa',
        ];
    }

    /**
     * =====================================================
     * STYLE EXCEL
     * =====================================================
     */
    public function styles(Worksheet $sheet)
    {
        // Header: bold & center
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');

        // Semua cell vertical center
        $sheet->getStyle('A:H')->getAlignment()->setVertical('center');

        // Kolom angka center
        $sheet->getStyle('E:H')->getAlignment()->setHorizontal('center');

        return [];
    }
}
