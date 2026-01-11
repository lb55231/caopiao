<?php
/**
 * 获取玩法和赔率配置API
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// K3游戏的8种主要玩法配置
$plays = [
    'k3hzzx' => [
        'id' => 'k3hzzx',
        'name' => '和值',
        'description' => '投注三个号码之和',
        'minOdds' => 1.96,
        'maxOdds' => 215.0,
        'minBet' => 2,
        'numbers' => range(3, 18), // 3-18点
        'odds' => [
            '3' => 215.0, '4' => 107.5, '5' => 53.75, '6' => 35.83,
            '7' => 26.88, '8' => 23.32, '9' => 21.5, '10' => 19.35,
            '11' => 19.35, '12' => 21.5, '13' => 23.32, '14' => 26.88,
            '15' => 35.83, '16' => 53.75, '17' => 107.5, '18' => 215.0
        ]
    ],
    'k3sthtx' => [
        'id' => 'k3sthtx',
        'name' => '三同号通选',
        'description' => '三个号码相同（豹子）',
        'odds' => 35.71,
        'minBet' => 2,
        'numbers' => ['通选'],
        'rule' => '111, 222, 333, 444, 555, 666任意一个'
    ],
    'k3sthdx' => [
        'id' => 'k3sthdx',
        'name' => '三同号单选',
        'description' => '指定三个号码相同',
        'odds' => 215.0,
        'minBet' => 2,
        'numbers' => ['111', '222', '333', '444', '555', '666']
    ],
    'k3sbthbz' => [
        'id' => 'k3sbthbz',
        'name' => '三不同号',
        'description' => '三个号码各不相同',
        'odds' => 35.71,
        'minBet' => 2,
        'numbers' => range(1, 6),
        'selectCount' => 3,
        'rule' => '从1-6中任选3个不同号码'
    ],
    'k3slhtx' => [
        'id' => 'k3slhtx',
        'name' => '三连号通选',
        'description' => '三个连续号码',
        'odds' => 11.91,
        'minBet' => 2,
        'numbers' => ['通选'],
        'rule' => '123, 234, 345, 456任意一个'
    ],
    'k3ethfx' => [
        'id' => 'k3ethfx',
        'name' => '二同号复选',
        'description' => '两个号码相同+任意第三个',
        'odds' => 11.91,
        'minBet' => 2,
        'numbers' => ['11', '22', '33', '44', '55', '66'],
        'rule' => '如选11，开奖号码含两个1即中奖'
    ],
    'k3ethdx' => [
        'id' => 'k3ethdx',
        'name' => '二同号单选',
        'description' => '指定两个相同+指定单个',
        'odds' => 71.43,
        'minBet' => 2,
        'numbers' => range(1, 6),
        'selectCount' => [2, 1],
        'rule' => '先选对子，再选单个号码'
    ],
    'k3ebthbz' => [
        'id' => 'k3ebthbz',
        'name' => '二不同号',
        'description' => '两个号码不相同',
        'odds' => 5.95,
        'minBet' => 2,
        'numbers' => range(1, 6),
        'selectCount' => 2,
        'rule' => '从1-6中任选2个不同号码'
    ]
];

Database::success('获取成功', $plays);

