<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['user_id', 'recommendation_item_id', 'rating_1_5', 'comment'];

    public function user()
    {return $this->belongsTo(User::class);}
    public function item()
    {return $this->belongsTo(RecommendationItem::class, 'recommendation_item_id');}
}
