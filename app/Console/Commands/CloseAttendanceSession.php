<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Log;

class CloseAttendanceSession extends Command
{
    protected $signature = 'attendance:close';

    protected $description = 'Menutup sesi absensi dan meng-alfa-kan mahasiswa yang belum absen';

    public function handle()
    {
        $now = now();

        $sessions = AttendanceSession::where('is_closed', false)->get();

        foreach ($sessions as $session) {

            // 🔔 REMINDER 15 MENIT
            if ($now->diffInMinutes($session->end_time, false) === 15) {
                $students = Student::where('teacher_id', $session->teacher_id)->get();

                foreach ($students as $student) {
                    if ($student->phone) {
                        $this->sendWa(
                            $student->phone,
                            "⏰ *REMINDER ABSENSI*\n\n"
                            ."Absensi akan ditutup jam {$session->end_time->format('H:i')}.\n"
                            ."Segera kirim lokasi jika belum absen."
                        );
                    }
                }
            }

            // ❌ TUTUP & ALFA
            if ($now->greaterThanOrEqualTo($session->end_time)) {

                Attendance::whereDate('date', $session->date)
                    ->where('status', 'belum_absen')
                    ->whereHas('student', fn ($q) =>
                        $q->where('teacher_id', $session->teacher_id)
                    )
                    ->update([
                        'status' => 'alfa',
                        'note'   => 'Tidak absen sampai batas waktu',
                    ]);

                $session->update(['is_closed' => true]);
            }
        }
    }

}
