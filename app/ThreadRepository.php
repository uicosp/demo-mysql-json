<?php
/**
 * @author: 鱼肚 <uicosp@gmail.com>
 * @date: 2017/8/3
 */

namespace App;


use Carbon\Carbon;
use DB;

class ThreadRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Thread();
    }

    public function getThreadList($page, $size = 10)
    {
        return $this->model->select('id', 'subject')->paginate(10);

    }

    public function getThreadListWithLike($page, $size=10)
    {
        sql();
        $threads= $this->getThreadList($page);
        $tids = array_pluck($threads->getCollection()->toArray(),'id');
        $likeList = $this->getLikeUserListBatch($tids);
        $threads->getCollection()->transform(function($item,$key) use($likeList){
            $item->like_list = $likeList[$key];
            return $item;
        });
        return $threads;
    }
    /**
     * 搜索 json path
     * @param $tid
     * @param $uid
     * @return mixed
     */
    protected function jsonSearchPath($tid, $uid)
    {
        return $this->model->selectRaw("JSON_SEARCH(like_user_list,'one',{$uid}) as path")
            ->where('id', $tid)->value('path');
    }

    /**
     * 是否已经点赞
     * @param $tid
     * @param $uid
     * @return bool
     */
    protected function hasLiked($tid, $uid)
    {
        if ($this->jsonSearchPath($tid, $uid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加点赞
     * @param $tid
     * @param $uid
     * @return bool
     */
    public function addLike($tid, $uid)
    {
        if ($this->hasLiked($tid, $uid)) {
            return true;
        }
        $item = [
            'id' => (string)$uid,
            'time' => Carbon::now()->toDateTimeString()
        ];
        $json = json_encode($item);
        return $this->model->where('id', $tid)->update(['like_user_list' => DB::raw("JSON_ARRAY_APPEND(like_user_list,'$',CAST('{$json}' as JSON))")]);
    }

    /**
     * 移除点赞
     * @param $tid
     * @param $uid
     * @return bool
     */
    public function removeLike($tid, $uid)
    {
        $path = $this->jsonSearchPath($tid, $uid);
        if (is_null($path)) {
            return true;
        }
        $path = str_replace('.id', '', $path);
        return $this->model->where('id', $tid)->update(['like_user_list' => DB::raw("JSON_REMOVE(like_user_list,{$path})")]);
    }

    /**
     * 获取点赞用户列表（分页）
     * @param $tid
     * @param $page
     * @param int $size
     * @return array
     */
    public function getLikeUserList($tid, $page, $size = 10)
    {
        $total = $this->model->where('id', $tid)->selectRaw("JSON_LENGTH(like_user_list) as length")->value('length');
        $pages = ceil($total / $size);

        $start = ($page - 1) * $size;
        $end = $page * $size - 1;
        $indexArr = [];
        // 倒序取反
        $start = $total - $start;
        $end = $total - $end;
        for ($i = $start; $i >= $end; $i--) {
            $indexArr[] = "'$[{$i}]'";
        }
        $indexStr = implode(',', $indexArr);
        $data = $this->model->where('id', $tid)->selectRaw("JSON_EXTRACT(like_user_list,{$indexStr}) as list")->value("list");
        $data = json_decode($data, true);
        return compact('pages', 'page', 'data');
    }

    /**
     * 批量获取点赞用户列表
     * @param $tids
     * @return array
     */
    public function getLikeUserListBatch(array $tids)
    {

        $total = 10;
        $start = 0;
        $end = 10;
        $indexArr = [];
        // 倒序取反
        $start = $total - $start;
        $end = $total - $end;
        for ($i = $start; $i >= $end; $i--) {
            $indexArr[] = "'$[{$i}]'";
        }
        $indexStr = implode(',', $indexArr);
        $data = $this->model->whereIn('id', $tids)->selectRaw("JSON_EXTRACT(like_user_list,{$indexStr}) as list")->get();
        // 将 list json字符串解码为 PHP 数组
        $data->transform(function ($item) {
            $item->list = json_decode($item->list, true);
            return $item;
        });

        return array_pluck($data->toArray(), 'list');
    }
}