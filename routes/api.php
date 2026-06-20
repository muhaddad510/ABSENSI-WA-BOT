<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotWebhookController;



Route::post('/bot/webhook', [BotWebhookController::class, 'handle'])
    ->name('bot.webhook');
