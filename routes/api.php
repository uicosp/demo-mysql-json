<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::any("/test","TestController@index");
Route::get("/threads","ThreadController@getThreadList"); // 获取帖子列表(包含点赞信息)
Route::post("/threads/{thread}/like","ThreadController@like"); // 点赞
Route::delete("/threads/{thread}/like","ThreadController@unlike"); // 取消点赞
Route::get("/users/{uid}/likes","UserController@getLikeList"); // 获取用户点赞列表
