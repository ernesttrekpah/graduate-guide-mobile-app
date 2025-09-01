<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationRun extends Model
{
    protected $fillable = ['user_id', 'rule_set_version_id', 'top_n', 'profile_snapshot_json', 'generated_at'];
    protected $casts    = ['profile_snapshot_json' => 'array', 'generated_at' => 'datetime'];

    public function items()
    {return $this->hasMany(RecommendationItem::class, 'run_id');}
    public function version()
    {return $this->belongsTo(RuleSetVersion::class, 'rule_set_version_id');}
}
