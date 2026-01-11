<?php
// +----------------------------------------------------------------------
// | 路由设置
// +----------------------------------------------------------------------

return [
    // 是否强制使用路由
    'url_route_must'        => false,
    // 合并路由规则
    'route_rule_merge'      => false,
    // 路由使用完整匹配
    'route_complete_match'  => false,
    // 使用注解路由
    'route_annotation'      => false,
    // 是否开启路由缓存
    'route_check_cache'     => false,
    // 是否开启路由延迟解析
    'url_lazy_route'        => false,
    // 是否开启路由自动完整匹配
    'route_auto_rule'       => true,
    // 默认的路由变量规则
    'default_route_pattern' => '[\w\.]+',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'         => false,
    // 请求缓存有效期
    'request_cache_expire'  => 0,
    // 全局请求缓存排除规则
    'request_cache_except'  => [],
    // 默认控制器层名称
    'controller_layer'      => 'controller',
    // 空控制器名
    'empty_controller'      => 'Error',
    // 是否使用控制器后缀
    'controller_suffix'     => false,
    // 默认操作名
    'default_action'        => 'index',
    // 操作方法前缀
    'use_action_prefix'     => false,
    // 操作方法后缀
    'action_suffix'         => '',
    // 非法操作名
    'empty_action'          => 'miss',
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'          => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'        => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'         => '/',
    // HTTPS代理标识
    'https_agent_name'      => '',
    // domain根域名
    'url_domain_root'       => '',
    // URL伪静态后缀
    'url_html_suffix'       => 'html',
    // 访问控制允许
    'url_domain_deploy'     => false,
    // 路由是否完全匹配
    'route_complete_match'  => false,
];

