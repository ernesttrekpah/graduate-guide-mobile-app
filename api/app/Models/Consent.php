<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    protected $fillable = ['user_id', 'policy_version', 'granted_at'];
    protected $casts    = ['granted_at' => 'datetime'];
    public function user()
    {return $this->belongsTo(User::class);}
}
