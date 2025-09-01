<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ExamResult extends Model
{
    protected $fillable = ['profile_id', 'exam_type', 'sitting_year', 'is_current'];
    protected $casts    = ['is_current' => 'boolean'];

    public function profile()
    {return $this->belongsTo(StudentProfile::class, 'profile_id');}
    public function subjects()
    {return $this->hasMany(ExamSubject::class);}
}

