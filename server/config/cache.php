<?php
return [
    // 默认缓存驱动
    'default' => 'file',

    // 缓存连接方式
    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '',
            'prefix'     => '',
            'expire'     => 0,
            'serialize'  => [],
        ],
    ],
];
