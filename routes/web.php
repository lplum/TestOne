<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


/** 
 *   子域名  
 */
Route::domain('index.laravel.com')->namespace('Index')->group(function () {
    
    #1月10号    登录视图
    Route::get('log',"IndexController@log");
    Route::any('logDo',"IndexController@logDo");
    Route::any('wechat',"IndexController@wechat");
    Route::any('huifu',"IndexController@huifu");
  
});

Route::domain('index.laravel.com')->namespace('Index')->middleware('CheckLogin')->group(function () {
    Route::get('list',"IndexController@list");
});


