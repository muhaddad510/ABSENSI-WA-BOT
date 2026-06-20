<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

   protected $fillable = [
    'teacher_id',
    'name',
    'course_name',
    'code',
    'semester',
    'university',
];



    // 1 kelas punya banyak mahasiswa
    public function students()
    {
        return $this->hasMany(Student::class, 'class_room_id');
    }

    public function teacher()
    {
    return $this->belongsTo(User::class, 'teacher_id');
    }


}
