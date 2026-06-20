<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send(string $target, string $message): void
    {
        $token = env('FONNTE_TOKEN');
        if (!$token) {
            Log::warning('FONNTE_TOKEN belum diset');
            return;
        }

        try {
            (new Client())->post('https://api.fonnte.com/send', [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target' => $target,
                    'message' => $message,
                ],
                'timeout' => 10,
            ]);

            Log::info('WA_SENT', [
                'to' => $target,
                'msg' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('WA_FAILED', ['err' => $e->getMessage()]);
        }
    }
}
