<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'teacher_id',
        'name',
        'nim',
        'phone',
        'class_room_id',
    ];

    public function classRoom()
    {
    return $this->belongsTo(\App\Models\ClassRoom::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function teacher()
    {
    return $this->belongsTo(User::class, 'teacher_id');
    }

}

