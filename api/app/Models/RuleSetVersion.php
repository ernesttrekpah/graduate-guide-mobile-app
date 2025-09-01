<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleSetVersion extends Model
{
    protected $fillable = ['rule_set_id', 'version', 'definition_json', 'weights_json', 'published_at', 'published_by', 'change_note'];
    protected $casts    = ['definition_json' => 'array', 'weights_json' => 'array', 'published_at' => 'datetime'];

    public function ruleSet()
    {return $this->belongsTo(RuleSet::class);}
}
