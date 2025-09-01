<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgrammeRequirementSet extends Model
{
    protected $fillable = ['programme_id', 'kind'];
    public function programme()
    {return $this->belongsTo(Programme::class);}
    public function items()
    {return $this->hasMany(ProgrammeRequirementItem::class, 'set_id');}
}

