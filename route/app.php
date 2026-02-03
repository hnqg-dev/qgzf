<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


// 受保护页面路由
Route::get('/', 'index/index/index')->middleware(\app\middleware\AuthCheck::class);
Route::get('zhangben', 'index/zhangben/index')->middleware(\app\middleware\AuthCheck::class);
Route::get('jiedai', 'index/jiedai/index')->middleware(\app\middleware\AuthCheck::class);
Route::get('wode', 'index/wode/index')->middleware(\app\middleware\AuthCheck::class);
Route::post('wode', 'index/wode/index')->middleware(\app\middleware\AuthCheck::class);
Route::post('wode/changepassword', 'index/wode/changepassword')->middleware(\app\middleware\AuthCheck::class);
Route::post('wode/clearCache', 'index/wode/clearCache')->middleware(\app\middleware\AuthCheck::class);
Route::post('wode/logout', 'index/wode/logout')->middleware(\app\middleware\AuthCheck::class);
Route::get('tongji', 'index/tongji/index')->middleware(\app\middleware\AuthCheck::class);
Route::get('tongji/getStatsData', 'index/tongji/getStatsData')->middleware(\app\middleware\AuthCheck::class);
Route::get('tongji/getLevel2Data', 'index/tongji/getLevel2Data')->middleware(\app\middleware\AuthCheck::class);


// 确保路由配置正确
Route::group('index', function () {
    // Route::any('/', 'index/index/index');
    Route::any('login', 'index/index/login');
    //测试链接
    Route::any('/', 'index/test'); 
    Route::any('debug', 'debug/index');
    // 其他路由...
});

// 如果有admin应用的路由
Route::group('admin', function () {
    // admin路由...
});