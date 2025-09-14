<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationItem extends Model
{
    protected $fillable = ['run_id', 'programme_id', 'total_score', 'component_scores_json', 'explanation_json', 'action_plan_text'];
    protected $casts    = ['component_scores_json' => 'array', 'explanation_json' => 'array'];

    protected $appends = ['score', 'component_scores', 'explanations'];

    public function getScoreAttribute(): float
    {
        return (float) $this->total_score; // DB already stores 0–100
    }
    public function getComponentScoresAttribute(): array
    {
        return (array) $this->component_scores_json;
    }
    public function getExplanationsAttribute(): array
    {
        return (array) $this->explanation_json;
    }

    public function run()
    {return $this->belongsTo(RecommendationRun::class, 'run_id');}
    public function programme()
    {return $this->belongsTo(Programme::class);}

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'recommendation_item_id');
    }

    // public function jobProspect(){
    //     return $this->belongsTo(JobProspect::class,'');
    // }

}
