<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ClassRoomController,
    StudentController,
    MonitoringController,
    TeachingLocationController,
    BotWebhookController,
    ReportController
};

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/login'));

// BOT WEBHOOK (NO AUTH)

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS (ADMIN & DOSEN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | STUDENTS
    |--------------------------------------------------------------------------
    */
    Route::resource('students', StudentController::class)
        ->except(['show']);

    Route::post('/students/import', [StudentController::class, 'import'])
        ->name('students.import');

    Route::get('/students/template/download', [StudentController::class, 'downloadTemplate'])
        ->name('students.template.download');

    /*
    |--------------------------------------------------------------------------
    | CLASS ROOMS
    |--------------------------------------------------------------------------
    */
    Route::resource('class-rooms', ClassRoomController::class)
        ->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | MONITORING ABSENSI
    |--------------------------------------------------------------------------
    */
    Route::get('/monitoring', [MonitoringController::class, 'index'])
        ->name('monitoring.index');

    Route::post('/monitoring/start', [MonitoringController::class, 'start'])
        ->name('monitoring.start');

    Route::post('/monitoring/stop', [MonitoringController::class, 'stop'])
        ->name('monitoring.stop');

    Route::post('/monitoring/close', [MonitoringController::class, 'close'])
        ->name('monitoring.close');

    Route::post('/monitoring/{attendance}/status', [MonitoringController::class, 'updateStatus'])
        ->name('monitoring.updateStatus');

    Route::get('/monitoring/{student}', [MonitoringController::class, 'show'])
        ->name('monitoring.show');

    /*
    |--------------------------------------------------------------------------
    | TEACHING LOCATION
    |--------------------------------------------------------------------------
    */
    Route::get('/teaching-location', [TeachingLocationController::class, 'index'])
        ->name('teaching-location.index');

    Route::post('/teaching-location', [TeachingLocationController::class, 'store'])
        ->name('teaching-location.store');

    /*
    |--------------------------------------------------------------------------
    | REPORT / LAPORAN
    |--------------------------------------------------------------------------
    | ✅ ADMIN & DOSEN BISA AKSES
    | (filter keamanan di controller)
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
        ->name('reports.exportExcel');

    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
        ->name('reports.exportPdf');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {

        Route::view('/bot-settings', 'bot_settings.index')
            ->name('bot-settings.index');

    });

});

require __DIR__ . '/auth.php';
