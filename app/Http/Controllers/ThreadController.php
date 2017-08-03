<?php

namespace App\Http\Controllers;

use App\LikeService;
use App\Thread;
use App\ThreadRepository;
use DB;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function getThreadList()
    {
        $threadRepository = app(ThreadRepository::class);

    }
    public function like(Thread $thread, Request $request)
    {
        sql();
        $this->validate($request, [
            'uid' => 'required'
        ]);
        DB::transaction(function () use ($thread, $request) {
            return app(LikeService::class)->like($request->input('uid'), $thread->id);
        });
        return "success";
    }

    public function unlike(Thread $thread, Request $request)
    {
        sql();
        $this->validate($request, [
            'uid' => 'required'
        ]);
        DB::transaction(function () use ($thread, $request) {
            return app(LikeService::class)->unlike($request->input('uid'), $thread->id);
        });
        return "success";
    }
}
