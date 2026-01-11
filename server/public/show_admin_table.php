<?php
require 'common/Database.php';
$pdo = Database::getInstance();
$prefix = Database::getPrefix();
$stmt = $pdo->query("SHOW COLUMNS FROM {$prefix}adminmember");
echo "caipiao_adminmember 表字段：" . PHP_EOL;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")" . PHP_EOL;
}

