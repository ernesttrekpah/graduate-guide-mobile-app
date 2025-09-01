<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $fillable = [
        'faculty_id', 'interest_area_id', 'name', 'course_type', 'aggregate_cutoff', 'additional_requirements_text',
    ];

    public function faculty()
    {return $this->belongsTo(Faculty::class);}
    public function institution()
    {return $this->faculty?->institution();} // convenience accessor
    public function interestArea()
    {return $this->belongsTo(InterestArea::class);}
    public function flags()
    {
        return $this->belongsToMany(RequirementFlag::class, 'programme_requirement_flags', 'programme_id', 'flag_id');
    }

    public function requirementSets()
    {return $this->hasMany(ProgrammeRequirementSet::class);}
    public function coreRequirements()
    {return $this->hasOne(ProgrammeRequirementSet::class)->where('kind', 'core');}
    public function electiveRequirements()
    {return $this->hasOne(ProgrammeRequirementSet::class)->where('kind', 'elective');}
    public function jobProspects()
    {return $this->hasMany(JobProspect::class);}

    // Who saved this programme
    public function savedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'saved_programmes')
            ->withPivot('note')
            ->withTimestamps();
    }

}
