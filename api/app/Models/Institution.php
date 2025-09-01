<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = ['name', 'short_name', 'region', 'website'];
    public function faculties()
    {return $this->hasMany(Faculty::class);}
}
