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
Route::get("/threads","ThreadController@getThreadList");
Route::post("/threads/{thread}/like","ThreadController@like");
Route::delete("/threads/{thread}/like","ThreadController@unlike");
Route::get("/users/{uid}/likes","UserController@getLikeList");
