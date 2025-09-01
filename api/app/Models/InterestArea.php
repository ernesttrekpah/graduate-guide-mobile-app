<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterestArea extends Model
{
    protected $fillable = ['name'];
    public function programmes()
    {return $this->hasMany(Programme::class);}
}
