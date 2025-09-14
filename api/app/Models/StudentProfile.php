<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'gender',
        'graduation_year',
        'school_name',
        'region',
        'meta',
    ];
    protected $casts = [
        'graduation_year' => 'integer',
        'meta'            => 'array',
    ];

    public function user()
    {return $this->belongsTo(User::class);}
    public function examResults()
    {return $this->hasMany(ExamResult::class, 'profile_id');}
    public function currentExamResult()
    {
        return $this->hasOne(ExamResult::class, 'profile_id')->where('is_current', true);
    }
    public function interestAssessments()
    {return $this->hasMany(InterestAssessment::class, 'profile_id');}
}
