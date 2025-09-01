<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectAlias extends Model
{
    protected $fillable = ['subject_id', 'alias'];
    public function subject()
    {return $this->belongsTo(Subject::class);}
}
