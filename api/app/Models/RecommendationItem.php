<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationItem extends Model
{
    protected $fillable = ['run_id', 'programme_id', 'total_score', 'component_scores_json', 'explanation_json', 'action_plan_text'];
    protected $casts    = ['component_scores_json' => 'array', 'explanation_json' => 'array'];

    public function run()
    {return $this->belongsTo(RecommendationRun::class, 'run_id');}
    public function programme()
    {return $this->belongsTo(Programme::class);}

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'recommendation_item_id');
    }

}
