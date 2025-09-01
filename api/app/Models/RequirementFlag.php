<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementFlag extends Model
{
    protected $fillable = ['code', 'label'];
    public function programmes()
    {
        return $this->belongsToMany(Programme::class, 'programme_requirement_flags', 'flag_id', 'programme_id');
    }
}
