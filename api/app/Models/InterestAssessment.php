<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;




class InterestAssessment extends Model
{
    protected $fillable = ['profile_id', 'instrument_version'];
    public function profile()
    {return $this->belongsTo(StudentProfile::class, 'profile_id');}
    public function answers()
    {return $this->hasMany(InterestAnswer::class, 'assessment_id');}
}

