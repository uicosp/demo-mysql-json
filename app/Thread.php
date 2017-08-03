<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $casts = [
        'like_user_list' => 'array'
    ];
}
