<?php
require_once __DIR__ . '/common/Database.php';
$_GET['cpname'] = 'hebk3';
ob_start();
include __DIR__ . '/api/game/period.php';
$output = ob_get_clean();

echo "API返回内容:\n";
echo "==================\n";
echo $output;
echo "\n==================\n\n";

$data = json_decode($output, true);
if ($data && isset($data['data'])) {
    echo "解析后的数据:\n";
    echo "当前期: " . $data['data']['currFullExpect'] . "\n";
    echo "上期: " . $data['data']['lastFullExpect'] . "\n";
    echo "上期开奖: " . json_encode($data['data']['lastResult'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "倒计时: " . $data['data']['remainTime'] . " 秒\n";
}

