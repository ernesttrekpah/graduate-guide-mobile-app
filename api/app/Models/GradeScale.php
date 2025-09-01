<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    protected $fillable = ['name'];
    public function mappings()
    {return $this->hasMany(GradeMapping::class, 'scale_id');}
}
