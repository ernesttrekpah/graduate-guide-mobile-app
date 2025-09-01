<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeMapping extends Model
{
    protected $fillable = ['scale_id', 'label', 'numeric_value'];
    public function scale()
    {return $this->belongsTo(GradeScale::class, 'scale_id');}
}
