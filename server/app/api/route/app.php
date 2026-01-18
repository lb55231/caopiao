<?php
// +----------------------------------------------------------------------
// | API 应用路由配置
// +----------------------------------------------------------------------

use think\facade\Route;

// ==================== 后台管理路由 ====================
Route::rule('admin/login', '\app\api\controller\admin\AuthController@login', 'POST');

// 管理员管理
Route::rule('admin/admin/list', '\app\api\controller\admin\AdminController@list', 'GET');
Route::rule('admin/admin/add', '\app\api\controller\admin\AdminController@add', 'POST');
Route::rule('admin/admin/update/:id', '\app\api\controller\admin\AdminController@update', 'PUT|POST');
Route::rule('admin/admin/update', '\app\api\controller\admin\AdminController@update', 'PUT|POST');
Route::rule('admin/admin/delete/:id', '\app\api\controller\admin\AdminController@delete', 'DELETE');
Route::rule('admin/admin/delete', '\app\api\controller\admin\AdminController@delete', 'DELETE');
Route::rule('admin/admin/toggle_lock/:id', '\app\api\controller\admin\AdminController@toggleLock', 'POST|PUT');
Route::rule('admin/admin/toggle-lock', '\app\api\controller\admin\AdminController@toggleLock', 'POST|PUT');
Route::rule('admin/admin/groups', '\app\api\controller\admin\AdminController@groups', 'GET');
Route::rule('admin/change-password', '\app\api\controller\admin\AdminController@changePassword', 'POST');
Route::rule('admin/change-safecode', '\app\api\controller\admin\AdminController@changeSafecode', 'POST');

Route::rule('admin/member/list', '\app\api\controller\admin\MemberController@list', 'GET');
Route::rule('admin/member/update/:id', '\app\api\controller\admin\MemberController@update', 'PUT');
Route::rule('admin/member/toggle_lock/:id', '\app\api\controller\admin\MemberController@toggleLock', 'PUT');
Route::rule('admin/member/change_balance', '\app\api\controller\admin\MemberController@changeBalance', 'POST');
Route::rule('admin/member/change_point', '\app\api\controller\admin\MemberController@changePoint', 'POST');
Route::rule('admin/member/change_xima', '\app\api\controller\admin\MemberController@changeXima', 'POST');
Route::rule('admin/member/delete/:id', '\app\api\controller\admin\MemberController@delete', 'DELETE');
Route::rule('admin/member/kickout/:id', '\app\api\controller\admin\MemberController@kickout', 'POST');
Route::rule('admin/member/same_ip', '\app\api\controller\admin\MemberController@sameIp', 'GET');
Route::rule('admin/member/banks', '\app\api\controller\admin\MemberController@banks', 'GET');
Route::rule('admin/member/recharge_records', '\app\api\controller\admin\MemberController@rechargeRecords', 'GET');
Route::rule('admin/member/withdraw_records', '\app\api\controller\admin\MemberController@withdrawRecords', 'GET');
Route::rule('admin/member/game_records', '\app\api\controller\admin\MemberController@gameRecords', 'GET');

Route::rule('admin/membergroup/list', '\app\api\controller\admin\MemberGroupController@list', 'GET');
Route::rule('admin/membergroup/add', '\app\api\controller\admin\MemberGroupController@add', 'POST');
Route::rule('admin/membergroup/update/:id', '\app\api\controller\admin\MemberGroupController@update', 'PUT');
Route::rule('admin/membergroup/delete/:id', '\app\api\controller\admin\MemberGroupController@delete', 'DELETE');
Route::rule('admin/membergroup/toggle_status/:id', '\app\api\controller\admin\MemberGroupController@toggleStatus', 'PUT');

Route::rule('admin/agent/links', '\app\api\controller\admin\AgentController@links', 'GET');
Route::rule('admin/agent/link/add', '\app\api\controller\admin\AgentController@addLink', 'POST');
Route::rule('admin/agent/link/delete/:id', '\app\api\controller\admin\AgentController@deleteLink', 'DELETE');
Route::rule('admin/loginlog/list', '\app\api\controller\admin\AgentController@loginLogs', 'GET');

Route::rule('admin/fund/records', '\app\api\controller\admin\FinanceController@fundRecords', 'GET');

// 充值记录
Route::rule('admin/recharge/list', '\app\api\controller\admin\FinanceController@rechargeList', 'GET');
Route::rule('admin/recharge/audit/:id', '\app\api\controller\admin\FinanceController@rechargeAudit', 'POST');
Route::rule('admin/recharge/audit', '\app\api\controller\admin\FinanceController@rechargeAudit', 'POST');
Route::rule('admin/recharge/delete/:id', '\app\api\controller\admin\FinanceController@rechargeDelete', 'DELETE');
Route::rule('admin/recharge/delete', '\app\api\controller\admin\FinanceController@rechargeDelete', 'DELETE');

// 提现记录
Route::rule('admin/withdraw/list', '\app\api\controller\admin\FinanceController@withdrawList', 'GET');
Route::rule('admin/withdraw/audit/:id', '\app\api\controller\admin\FinanceController@withdrawAudit', 'POST');
Route::rule('admin/withdraw/audit', '\app\api\controller\admin\FinanceController@withdrawAudit', 'POST');
Route::rule('admin/withdraw/delete/:id', '\app\api\controller\admin\FinanceController@withdrawDelete', 'DELETE');
Route::rule('admin/withdraw/delete', '\app\api\controller\admin\FinanceController@withdrawDelete', 'DELETE');

Route::rule('admin/lottery/types', '\app\api\controller\admin\LotteryController@types', 'GET');
Route::rule('admin/lottery/results', '\app\api\controller\admin\LotteryController@results', 'GET');
Route::rule('admin/lottery/result/add', '\app\api\controller\admin\LotteryController@addResult', 'POST');
Route::rule('admin/lottery/result/delete/:id', '\app\api\controller\admin\LotteryController@deleteResult', 'DELETE');
Route::rule('admin/lottery/type/add', '\app\api\controller\admin\LotteryController@addType', 'POST');
Route::rule('admin/lottery/type/update/:id', '\app\api\controller\admin\LotteryController@updateType', 'PUT');
Route::rule('admin/lottery/type/delete/:id', '\app\api\controller\admin\LotteryController@deleteType', 'DELETE');
Route::rule('admin/lottery/toggle_status', '\app\api\controller\admin\LotteryController@toggleStatus', 'POST');
Route::rule('admin/lottery/toggle-status', '\app\api\controller\admin\LotteryController@toggleStatus', 'POST');
Route::rule('admin/lottery/save_order', '\app\api\controller\admin\LotteryController@saveOrder', 'POST');
Route::rule('admin/lottery/save-order', '\app\api\controller\admin\LotteryController@saveOrder', 'POST');
Route::rule('admin/bet/records', '\app\api\controller\admin\LotteryController@betRecords', 'GET');
Route::rule('admin/yukaijiang', '\app\api\controller\admin\LotteryController@yukaijiang', 'GET');
Route::rule('admin/ykjbaocun', '\app\api\controller\admin\LotteryController@ykjbaocun', 'POST');

// 存款方式配置（payset表）
Route::rule('admin/payset/list', '\app\api\controller\admin\BankController@paysetList', 'GET');
Route::rule('admin/payset/add', '\app\api\controller\admin\BankController@addPayset', 'POST');
Route::rule('admin/payset/update/:id', '\app\api\controller\admin\BankController@updatePayset', 'PUT');
Route::rule('admin/payset/update', '\app\api\controller\admin\BankController@updatePayset', 'PUT');
Route::rule('admin/payset/delete/:id', '\app\api\controller\admin\BankController@deletePayset', 'DELETE');
Route::rule('admin/payset/delete', '\app\api\controller\admin\BankController@deletePayset', 'DELETE');
Route::rule('admin/payset/toggle_status/:id', '\app\api\controller\admin\BankController@togglePaysetStatus', 'PUT');
Route::rule('admin/payset/toggle-status', '\app\api\controller\admin\BankController@togglePaysetStatus', 'POST');
Route::rule('admin/payset/listorder', '\app\api\controller\admin\BankController@savePaysetOrder', 'POST');

// 提款银行（sysbank表）
Route::rule('admin/sysbank/list', '\app\api\controller\admin\BankController@sysbankList', 'GET');
Route::rule('admin/sysbank/add', '\app\api\controller\admin\BankController@addSysbank', 'POST');
Route::rule('admin/sysbank/update/:id', '\app\api\controller\admin\BankController@updateSysbank', 'PUT');
Route::rule('admin/sysbank/update', '\app\api\controller\admin\BankController@updateSysbank', 'PUT');
Route::rule('admin/sysbank/delete/:id', '\app\api\controller\admin\BankController@deleteSysbank', 'DELETE');
Route::rule('admin/sysbank/delete', '\app\api\controller\admin\BankController@deleteSysbank', 'DELETE');
Route::rule('admin/sysbank/toggle_status/:id', '\app\api\controller\admin\BankController@toggleSysbankStatus', 'PUT');
Route::rule('admin/sysbank/toggle-status', '\app\api\controller\admin\BankController@toggleSysbankStatus', 'POST');

// 线路银行（linebank表）
Route::rule('admin/linebank/list', '\app\api\controller\admin\BankController@linebankList', 'GET');
Route::rule('admin/linebank/add', '\app\api\controller\admin\BankController@addLinebank', 'POST');
Route::rule('admin/linebank/update/:id', '\app\api\controller\admin\BankController@updateLinebank', 'PUT');
Route::rule('admin/linebank/update', '\app\api\controller\admin\BankController@updateLinebank', 'PUT');
Route::rule('admin/linebank/delete/:id', '\app\api\controller\admin\BankController@deleteLinebank', 'DELETE');
Route::rule('admin/linebank/delete', '\app\api\controller\admin\BankController@deleteLinebank', 'DELETE');
Route::rule('admin/linebank/toggle_status/:id', '\app\api\controller\admin\BankController@toggleLinebankStatus', 'PUT');
Route::rule('admin/linebank/toggle-status', '\app\api\controller\admin\BankController@toggleLinebankStatus', 'POST');

Route::rule('admin/settings', '\app\api\controller\admin\SystemController@settings', 'GET');
Route::rule('admin/settings/save', '\app\api\controller\admin\SystemController@saveSettings', 'POST');
Route::rule('admin/activity/list', '\app\api\controller\admin\SystemController@activities', 'GET');
Route::rule('admin/activity/add', '\app\api\controller\admin\SystemController@addActivity', 'POST');
Route::rule('admin/activity/update/:id', '\app\api\controller\admin\SystemController@updateActivity', 'PUT|POST');
Route::rule('admin/activity/delete/:id', '\app\api\controller\admin\SystemController@deleteActivity', 'DELETE');
Route::rule('admin/upload/image', '\app\api\controller\admin\SystemController@uploadImage', 'POST');

// 玩法管理
Route::rule('admin/play/list', '\app\api\controller\admin\PlayController@list', 'GET');
Route::rule('admin/play/update/:id', '\app\api\controller\admin\PlayController@update', 'PUT|POST');
Route::rule('admin/play/update', '\app\api\controller\admin\PlayController@update', 'PUT|POST');
Route::rule('admin/play/toggle-status', '\app\api\controller\admin\PlayController@toggleStatus', 'POST|PUT');
Route::rule('admin/play/batch-rate', '\app\api\controller\admin\PlayController@batchUpdateRate', 'POST');

// 注单异常检查
Route::rule('admin/bet/check', '\app\api\controller\admin\BetCheckController@check', 'GET');
Route::rule('admin/bet/cancel', '\app\api\controller\admin\BetCheckController@cancelBet', 'POST');
Route::rule('admin/bet/update-code', '\app\api\controller\admin\BetCheckController@updateBetCode', 'POST');

// ==================== 通用上传接口 ====================
Route::rule('upload/image', '\app\api\controller\UploadController@image', 'POST');

// ==================== 代理端路由 ====================
// 代理登录（不需要认证）
Route::rule('agent/login', '\app\api\controller\agent\AuthController@login', 'POST');

// 代理账户管理
Route::rule('agent/account/info', '\app\api\controller\agent\AccountController@info', 'GET');

// 代理财务管理
Route::rule('agent/finance/account-change', '\app\api\controller\agent\FinanceController@accountChange', 'GET');
Route::rule('agent/finance/recharge', '\app\api\controller\agent\FinanceController@rechargeList', 'GET');
Route::rule('agent/finance/withdraw', '\app\api\controller\agent\FinanceController@withdrawList', 'GET');
Route::rule('agent/finance/profit', '\app\api\controller\agent\FinanceController@profitReport', 'GET');

// 代理彩票管理
Route::rule('agent/lottery/types', '\app\api\controller\agent\LotteryController@types', 'GET');
Route::rule('agent/lottery/results', '\app\api\controller\agent\LotteryController@results', 'GET');
Route::rule('agent/lottery/bet-records', '\app\api\controller\agent\LotteryController@betRecords', 'GET');
Route::rule('agent/lottery/yukaijiang', '\app\api\controller\agent\LotteryController@yukaijiang', 'GET');
Route::rule('agent/lottery/save-yukaijiang', '\app\api\controller\agent\LotteryController@saveYukaijiang', 'POST');