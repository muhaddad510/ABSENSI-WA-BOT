<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\TeachingLocation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BotWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $senderRaw = $payload['sender']
            ?? $payload['pengirim']
            ?? $payload['from']
            ?? null;

        if (!$senderRaw) {
            return response()->json(['ok' => true]);
        }

        $sender = $this->normalizePhone($senderRaw);

        /**
         * =====================================================
         * 🔐 GLOBAL ATOMIC LOCK (ANTI SPAM / RACE CONDITION)
         * =====================================================
         */
        $lockKey = 'attendance_processing_' . $sender;

        // jika masih diproses request lain → DIAM TOTAL
        if (!Cache::add($lockKey, true, 8)) {
            return response()->json(['ok' => true]);
        }

        try {

            return DB::transaction(function () use ($payload, $sender) {

                $message = strtolower(trim(
                    $payload['message']
                    ?? $payload['pesan']
                    ?? ''
                ));

                [$lat, $lng] = $this->extractLocation($payload);

                // =========================
                // MAHASISWA
                // =========================
                $student = Student::where('phone', $sender)->first();
                if (!$student) {
                    return response()->json(['ok' => true]);
                }

                // =========================
                // ABSENSI HARI INI
                // =========================
                $attendance = Attendance::where('student_id', $student->id)
                    ->whereDate('date', Carbon::today())
                    ->lockForUpdate()
                    ->first();

                if (!$attendance) {
                    return response()->json(['ok' => true]);
                }

                /**
                 * =====================================================
                 * 🔒 ABSOLUTE FINAL SILENT LOCK (CASE A–G)
                 * =====================================================
                 */
                if (in_array($attendance->status, ['hadir','izin','sakit','alfa'])) {
                    return response()->json(['ok' => true]);
                }

                /**
                 * =====================================================
                 * IZIN / SAKIT (FINAL)
                 * =====================================================
                 */
                if (in_array($message, ['izin','sakit'])) {

                    $attendance->update([
                        'status'            => $message,
                        'note'              => ucfirst($message) . ' via WhatsApp',
                        'final_notified_at' => now(),
                    ]);

                    $this->sendFonnteMessage(
                        $sender,
                        "✅ Kamu dinyatakan *" . strtoupper($message) . "* (FINAL).\nHubungi dosen jika ada kesalahan."
                    );

                    return response()->json(['ok' => true]);
                }

                /**
                 * =====================================================
                 * BELUM ADA LOKASI
                 * =====================================================
                 */
                if (!$lat || !$lng) {
                    return response()->json(['ok' => true]);
                }

                /**
                 * =====================================================
                 * VALIDASI LOKASI DOSEN
                 * =====================================================
                 */
                $loc = TeachingLocation::where('user_id', $student->teacher_id)->first();
                if (!$loc) {
                    return response()->json(['ok' => true]);
                }

                $radius = $loc->radius_m ?? 200;

                $distance = (int) round(
                    $this->haversineDistanceMeters(
                        $loc->latitude,
                        $loc->longitude,
                        $lat,
                        $lng
                    )
                );

                $attendance->update([
                    'lat'        => $lat,
                    'lng'        => $lng,
                    'distance_m' => $distance,
                ]);

                /**
                 * =====================================================
                 * ❌ LUAR RADIUS (MAX 3x)
                 * =====================================================
                 */
                if ($distance > $radius) {

                    $attendance->increment('attempt_count');

                    if ($attendance->attempt_count >= 3) {

                        $attendance->update([
                            'status'            => 'alfa',
                            'note'              => 'Gagal absen 3x (di luar radius)',
                            'final_notified_at' => now(),
                        ]);

                        $this->sendFonnteMessage(
                            $sender,
                            "❌ Absen *DITOLAK 3x*.\nStatus akhir: *ALFA*."
                        );

                    } else {

                        $attendance->update([
                            'status' => 'absen_ditolak',
                            'note'   => "Di luar radius ({$distance} m)",
                        ]);

                        $this->sendFonnteMessage(
                            $sender,
                            "❌ Absen *DITOLAK* ({$attendance->attempt_count}/3).\nSilakan coba lagi."
                        );
                    }

                    return response()->json(['ok' => true]);
                }

                /**
                 * =====================================================
                 * ✅ HADIR (FINAL)
                 * =====================================================
                 */
                $attendance->update([
                    'status'            => 'hadir',
                    'check_in'          => now()->format('H:i:s'),
                    'note'              => 'Absen via WA lokasi',
                    'final_notified_at' => now(),
                ]);

                $this->sendFonnteMessage(
                    $sender,
                    "✅ Kamu dinyatakan *HADIR* (FINAL).\n📏 Jarak: {$distance} m"
                );

                return response()->json(['ok' => true]);
            });

        } finally {
            // 🔓 RELEASE GLOBAL LOCK
            Cache::forget($lockKey);
        }
    }

    // ========================= UTIL =========================

    private function extractLocation(array $payload): array
    {
        if (isset($payload['latitude'], $payload['longitude'])) {
            return [(float)$payload['latitude'], (float)$payload['longitude']];
        }

        if (isset($payload['location']) && is_string($payload['location'])) {
            $parts = explode(',', $payload['location']);
            if (count($parts) === 2) {
                return [(float)$parts[0], (float)$parts[1]];
            }
        }

        return [null, null];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($phone, '62')
            ? $phone
            : '62' . ltrim($phone, '0');
    }

    private function haversineDistanceMeters($lat1, $lon1, $lat2, $lon2): float
    {
        $earth = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) *
             cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function sendFonnteMessage(string $target, string $message): void
    {
        $token = env('FONNTE_TOKEN');
        if (!$token) return;

        (new Client())->post('https://api.fonnte.com/send', [
            'headers' => ['Authorization' => $token],
            'form_params' => [
                'target'  => $target,
                'message' => $message,
            ],
        ]);
    }
}
