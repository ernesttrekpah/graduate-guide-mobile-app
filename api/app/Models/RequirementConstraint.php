<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementConstraint extends Model
{
    protected $fillable = ['item_id', 'scale_id', 'min_numeric_value', 'max_numeric_value', 'raw_text'];
    public function item()
    {return $this->belongsTo(ProgrammeRequirementItem::class, 'item_id');}
    public function scale()
    {return $this->belongsTo(GradeScale::class, 'scale_id');}
}

