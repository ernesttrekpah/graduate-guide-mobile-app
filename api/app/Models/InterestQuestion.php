<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InterestQuestion extends Model
{
    protected $fillable = ['text', 'domain', 'weight', 'active'];
    protected $casts    = ['weight' => 'decimal:2', 'active' => 'boolean'];
}
