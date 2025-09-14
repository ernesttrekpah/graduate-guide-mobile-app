<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobProspect extends Model
{
    protected $fillable = ['programme_id', 'title', 'description'];
    public function programme()
    {return $this->belongsTo(Programme::class);}
}
