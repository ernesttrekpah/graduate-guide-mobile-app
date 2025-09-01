<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InterestAnswer extends Model
{
    protected $fillable = ['assessment_id', 'question_id', 'value'];
    public function assessment()
    {return $this->belongsTo(InterestAssessment::class, 'assessment_id');}
    public function question()
    {return $this->belongsTo(InterestQuestion::class, 'question_id');}
}
