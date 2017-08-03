<?php

namespace App\Http\Controllers;

use App\LikeService;
use App\Thread;
use App\ThreadRepository;
use App\UserLike;
use App\UserLikeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{


    public function index(Request $request)
    {
//        sql();
        return app(ThreadRepository::class)->getThreadListWithLike(1);
    }
}
