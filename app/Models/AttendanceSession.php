<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $table = 'attendance_sessions';

    protected $fillable = [
        'teacher_id',
        'class_room_id',
        'date',
        'start_time',
        'end_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
