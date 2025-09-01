<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgrammeRequirementItem extends Model
{
    protected $fillable = ['set_id', 'subject_id', 'choice_group_id', 'required', 'weight'];
    public function set()
    {return $this->belongsTo(ProgrammeRequirementSet::class, 'set_id');}
    public function subject()
    {return $this->belongsTo(Subject::class);}
    public function choiceGroup()
    {return $this->belongsTo(ChoiceGroup::class);}
    public function constraints()
    {return $this->hasMany(RequirementConstraint::class, 'item_id');} // supports multiple scales
}

