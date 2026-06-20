<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'note',
        'lat',
        'lng',
        'distance_m',
    ];

    public function student()
    {
    return $this->belongsTo(\App\Models\Student::class);
    }

}

