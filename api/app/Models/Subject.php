<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['code', 'name', 'group'];
    public function aliases()
    {return $this->hasMany(SubjectAlias::class);}

    // in Subject.php
    public function choiceGroups()
    {
        return $this->belongsToMany(ChoiceGroup::class, 'choice_group_subjects');
    }

}
