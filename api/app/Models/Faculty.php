<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = ['institution_id', 'name'];
    public function institution()
    {return $this->belongsTo(Institution::class);}
    public function programmes()
    {return $this->hasMany(Programme::class);}
}
