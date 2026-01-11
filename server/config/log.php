<?php

// 日志配置
return [
    // 默认日志记录通道
    'default'      => 'file',
    // 日志记录级别
    'level'        => [],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => false,
    // 是否实时写入
    'realtime_write' => false,
    // 全局日志处理 支持闭包
    'processor'    => null,
    
    // 日志通道列表
    'channels'     => [
        'file' => [
            'type'           => 'File',
            'path'           => '',
            'level'          => [],
            'file_size'      => 2097152,
            'single'         => false,
            'apart_level'    => [],
            'max_files'      => 0,
            'json'           => false,
            'json_options'   => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            'format'         => '[%s][%s] %s',
            'realtime_write' => false,
        ],
    ],
];
