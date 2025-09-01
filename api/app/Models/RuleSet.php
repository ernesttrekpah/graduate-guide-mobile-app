<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleSet extends Model
{
    protected $fillable = ['name', 'active_version_id'];
    public function versions()
    {return $this->hasMany(RuleSetVersion::class);}
    public function activeVersion()
    {return $this->belongsTo(RuleSetVersion::class, 'active_version_id');}
}
