<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ExamSubject extends Model
{
    protected $fillable = ['exam_result_id', 'subject_id', 'grade_label', 'grade_numeric'];
    public function exam()
    {return $this->belongsTo(ExamResult::class, 'exam_result_id');}
    public function subject()
    {return $this->belongsTo(Subject::class);}
}
