<?php

namespace App\Console\Commands;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class SendAttendanceReminder extends Command
{
    protected $signature = 'attendance:reminder';
    protected $description = 'Kirim reminder WA absensi';

    public function handle()
    {
        $now = now();

        $sessions = AttendanceSession::where('is_closed', false)->get();

        foreach ($sessions as $session) {
            $end = now()->setTimeFromTimeString($session->end_time);

            $minutesLeft = $now->diffInMinutes($end, false);

            // ⏰ 30 MENIT
            if ($minutesLeft <= 30 && $minutesLeft > 10 && !$session->notified_30) {
                $this->sendReminder($session, 30);
                $session->update(['notified_30' => true]);
            }

            // ⏰ 10 MENIT
            if ($minutesLeft <= 10 && $minutesLeft > 0 && !$session->notified_10) {
                $this->sendReminder($session, 10);
                $session->update(['notified_10' => true]);
            }
        }
    }

    private function sendReminder($session, $minute)
    {
        $attendances = Attendance::whereDate('date', $session->date)
            ->where('status', 'belum_absen')
            ->whereHas('student', function ($q) use ($session) {
                $q->where('teacher_id', $session->teacher_id);
                if ($session->class_room_id) {
                    $q->where('class_room_id', $session->class_room_id);
                }
            })
            ->with('student')
            ->get();

        $client = new Client();
        $token = env('FONNTE_TOKEN');

        foreach ($attendances as $a) {
            if (!$a->student->phone) continue;

            $client->post('https://api.fonnte.com/send', [
                'headers' => ['Authorization' => $token],
                'form_params' => [
                    'target' => $a->student->phone,
                    'message' =>
                        "⏰ *REMINDER ABSENSI*\n\n" .
                        "Sisa waktu: *{$minute} menit*\n" .
                        "Segera kirim *lokasi realtime* untuk absen.\n\n" .
                        "📍 Attach → Location → Send current location"
                ],
                'timeout' => 10,
            ]);
        }
    }
}
