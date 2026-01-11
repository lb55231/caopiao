<?php
use think\facade\Route;

// ==================== API路由 ====================

// 公开接口（无需认证）
Route::group('api', function () {
    
    // 用户相关
    Route::post('user/login', 'api.User/login');
    Route::post('user/register', 'api.User/register');
    
    // 系统配置
    Route::get('system/config', 'api.System/config');
    
    // 彩票相关
    Route::get('lottery/list', 'api.Lottery/list');
    Route::get('lottery/detail', 'api.Lottery/detail');
    Route::get('lottery/current', 'api.Lottery/currentPeriod');
    Route::get('lottery/history', 'api.Lottery/history');
    
    // 需要认证的路由
    Route::group('', function () {
        // 用户信息
        Route::get('user/info', 'api.User/info');
        Route::get('user/profile', 'api.User/info');  // 兼容旧接口
        Route::post('user/profile', 'api.User/updateProfile');
        Route::post('user/change_password', 'api.User/changePassword');
        
        // 投注相关
        Route::post('game/bet', 'api.Game/bet');
        Route::get('game/records', 'api.Game/betRecords');
        
        // 账户相关
        Route::get('user/account_records', 'api.Account/records');
        Route::post('user/recharge/add', 'api.Account/recharge');
        Route::post('user/withdraw/add', 'api.Account/withdraw');
        
        // 银行卡管理
        Route::get('user/banklist', 'api.Account/bankList');
        Route::post('user/bank/add', 'api.Account/addBank');
        Route::post('user/bank/delete', 'api.Account/deleteBank');
        Route::post('user/bank/set_default', 'api.Account/setDefaultBank');
        
    })->middleware('api\middleware\Auth');
    
})->middleware('AllowCrossDomain');
