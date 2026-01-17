<?php
// +----------------------------------------------------------------------
// | 全局中间件定义
// +----------------------------------------------------------------------

return [
    // 跨域已在 public/index.php 入口文件中统一处理
    // Token验证和用户ID注入
    \app\common\middleware\Auth::class,
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    \think\middleware\SessionInit::class,
];

