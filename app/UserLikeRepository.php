<?php
/**
 * @author: 鱼肚 <uicosp@gmail.com>
 * @date: 2017/8/2
 */

namespace App;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserLikeRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserLike();
    }

    protected function createIfNotExists($uid)
    {
        return $this->model->firstOrCreate(['user_id' => $uid], ['like_thread_list' => []]);
    }

    /**
     * 搜索 json path
     * @param $uid
     * @param $tid
     * @return mixed
     */
    protected function jsonSearchPath($uid, $tid)
    {
        return $this->model->selectRaw("JSON_SEARCH(like_thread_list,'one',{$tid}) as path")
            ->where('user_id', $uid)->value('path');
    }

    /**
     * 是否已经点赞
     * @param $uid
     * @param $tid
     * @return bool
     */
    protected function hasLiked($uid, $tid)
    {
        if ($this->jsonSearchPath($uid, $tid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加点赞
     * @param $uid
     * @param $tid
     * @return bool
     */
    public function addLike($uid, $tid)
    {
        // 首先创建记录
        $this->createIfNotExists($uid);

        if ($this->hasLiked($uid, $tid)) {
            return true;
        }
        $item = [
            'id' => (string)$tid,
            'time' => Carbon::now()->toDateTimeString()
        ];
        $json = json_encode($item);
        return $this->model->where('user_id', $uid)->update(['like_thread_list' => DB::raw("JSON_ARRAY_APPEND(like_thread_list,'$',CAST('{$json}' as JSON))")]);
    }

    /**
     * 移除点赞
     * @param $uid
     * @param $tid
     * @return bool
     */
    public function removeLike($uid, $tid)
    {
        $path = $this->jsonSearchPath($uid, $tid);
        if (is_null($path)) {
            return true;
        }
        $path = str_replace('.id', '', $path);
        return $this->model->where('user_id', $uid)->update(['like_thread_list' => DB::raw("JSON_REMOVE(like_thread_list,{$path})")]);
    }

    /**
     * 获取用户的点赞列表（分页）
     * @param $uid
     * @param $page
     * @param int $size
     * @return array
     */
    public function getLikeThreadList($uid, $page, $size = 10)
    {
        $total = $this->model->where('user_id', $uid)->selectRaw("JSON_LENGTH(like_thread_list) as length")->value('length');
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
        $data = $this->model->where('user_id', $uid)->selectRaw("JSON_EXTRACT(like_thread_list,{$indexStr}) as list")->value("list");
        $data = json_decode($data, true);
        return compact('pages', 'page', 'data');
    }
}