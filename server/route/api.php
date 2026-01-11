<?php
use think\facade\Route;

// ==================== API路由 ====================

// 特殊路径 - 优先匹配（必须在路由组外定义）
Route::get('api/user/payset/list', 'api.User/paysetList');
Route::get('api/user/bank/list', 'api.User/banklist');
Route::post('api/user/bank/add', 'api.User/bankAdd');
Route::post('api/user/bank/delete', 'api.User/bankDelete');
Route::post('api/user/bank/setdefault', 'api.User/bankSetDefault');
Route::get('api/user/account/records', 'api.User/accountRecords');
Route::get('api/user/bet/records', 'api.User/betRecords');
Route::post('api/user/recharge/add', 'api.User/rechargeAdd');
Route::post('api/user/withdraw/add', 'api.User/withdrawAdd');
Route::post('api/user/profile/update', 'api.User/profileUpdate');
Route::put('api/user/profile', 'api.User/updateProfile');
Route::post('api/user/change/password', 'api.User/changePassword');

Route::group('api', function () {
    
    // 系统配置（公开）
    Route::get('system/config', 'api.System/config');
    
    // 彩票相关（公开）
    Route::get('lottery/list', 'api.Lottery/list');
    Route::get('lottery/detail', 'api.Lottery/detail');
    Route::get('lottery/current', 'api.Lottery/currentPeriod');
    Route::get('lottery/history', 'api.Lottery/history');
    Route::get('lottery/notice', 'api.Lottery/notice');
    Route::get('lottery/ranking', 'api.Lottery/ranking');
    Route::rule('lottery/auto_kaijiang', 'api.Lottery/autoKaijiang', 'GET');
    Route::get('lottery/settlement', 'api.Lottery/settlement');
    
    // 游戏相关（公开）
    Route::get('game/period', 'api.Game/period');
    Route::get('game/plays', 'api.Game/plays');
    Route::post('game/bet', 'api.Game/bet');
    
    // 上传相关
    Route::post('upload/image', 'api.UploadController/image');
    
    // 用户相关（公开）
    Route::post('user/login', 'api.User/login');
    Route::post('user/register', 'api.User/register');
    
    // 用户相关（需要认证）
    Route::get('user/info', 'api.User/info');
    Route::get('user/profile', 'api.User/profile');
    
});
