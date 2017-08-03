<?php

namespace App\Http\Controllers;

use App\UserLikeRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getLikeList($uid, Request $request)
    {
        $page = $request->input('page');
        return app(UserLikeRepository::class)->getLikeThreadList($uid,$page);
    }
}
