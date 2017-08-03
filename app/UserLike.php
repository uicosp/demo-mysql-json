<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLike extends Model
{
    protected $fillable = ['user_id', 'like_thread_list'];
    protected $casts = [
        'like_thread_list' => 'array'
    ];
}
