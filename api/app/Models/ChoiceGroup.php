<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoiceGroup extends Model
{
    protected $fillable = ['min_required'];
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'choice_group_subjects');
    }
}
