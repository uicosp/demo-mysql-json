<?php

namespace App\Http\Controllers;

use App\LikeService;
use App\Thread;
use App\ThreadRepository;
use DB;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * 获取帖子列表，包括点赞信息
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getThreadList(Request $request)
    {
        $page = $request->get('page', 1);
        $threadRepository = app(ThreadRepository::class);
        return $threadRepository->getThreadListWithLike($page);
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
