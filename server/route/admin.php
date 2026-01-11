<?php
// +----------------------------------------------------------------------
// | 管理后台路由
// +----------------------------------------------------------------------

use think\facade\Route;

// 管理员登录
Route::post('admin/login', 'admin.Login/index');

// 需要管理员权限的接口
Route::group('admin', function () {
    // 仪表盘
    Route::get('dashboard', 'admin.Index/dashboard');
    
    // 用户管理
    Route::get('user/list', 'admin.User/list');
    Route::get('user/detail/:id', 'admin.User/detail');
    Route::post('user/update', 'admin.User/update');
    Route::post('user/freeze', 'admin.User/freeze');
    
    // 彩票管理
    Route::get('lottery/list', 'admin.Lottery/list');
    Route::post('lottery/add', 'admin.Lottery/add');
    Route::post('lottery/update', 'admin.Lottery/update');
    Route::post('lottery/delete', 'admin.Lottery/delete');
    
    // 开奖管理
    Route::get('result/list', 'admin.Result/list');
    Route::post('result/add', 'admin.Result/add');
    Route::post('result/update', 'admin.Result/update');
    
    // 订单管理
    Route::get('order/list', 'admin.Order/list');
    Route::get('order/detail/:id', 'admin.Order/detail');
    Route::post('order/settle', 'admin.Order/settle');
    
    // 充值提现管理
    Route::get('finance/recharge', 'admin.Finance/recharge');
    Route::get('finance/withdraw', 'admin.Finance/withdraw');
    Route::post('finance/audit', 'admin.Finance/audit');
    
    // 系统配置
    Route::get('config/list', 'admin.Config/list');
    Route::post('config/save', 'admin.Config/save');
    
    // 采集管理
    Route::get('collect/list', 'admin.Collect/list');
    Route::post('collect/start', 'admin.Collect/start');
    Route::post('collect/stop', 'admin.Collect/stop');
    
})->middleware([
    \app\admin\middleware\Auth::class,
    \app\admin\middleware\Permission::class
]);

