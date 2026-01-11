<?php
/**
 * K3游戏开奖结算API
 * 根据开奖结果自动结算投注记录
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

try {
    // 获取所有未结算的期号（有开奖结果但投注未结算）
    $sql = "
        SELECT DISTINCT t.cpname, t.expect 
        FROM {$prefix}touzhu t
        INNER JOIN {$prefix}kaijiang k ON t.cpname = k.name AND t.expect = k.expect
        WHERE t.isdraw = 0 AND k.opencode IS NOT NULL AND k.opencode != ''
        LIMIT 10
    ";
    
    $stmt = $pdo->query($sql);
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settledCount = 0;
    $settledPeriods = [];
    
    foreach ($periods as $period) {
        $cpname = $period['cpname'];
        $expect = $period['expect'];
        
        // 获取该期开奖号码
        $kjStmt = $pdo->prepare("SELECT opencode FROM {$prefix}kaijiang WHERE name = :cpname AND expect = :expect LIMIT 1");
        $kjStmt->execute([':cpname' => $cpname, ':expect' => $expect]);
        $kjResult = $kjStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$kjResult || empty($kjResult['opencode'])) {
            continue;
        }
        
        $opencode = $kjResult['opencode'];
        $opencodes = explode(',', $opencode);
        $sum = array_sum($opencodes);
        
        // 获取该期所有未结算的投注
        $tzStmt = $pdo->prepare("
            SELECT id, uid, username, playid, tzcode, amount, mode 
            FROM {$prefix}touzhu 
            WHERE cpname = :cpname AND expect = :expect AND isdraw = 0
        ");
        $tzStmt->execute([':cpname' => $cpname, ':expect' => $expect]);
        $bets = $tzStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 开始事务
        $pdo->beginTransaction();
        
        foreach ($bets as $bet) {
            $isWin = checkWin($cpname, $bet['playid'], $bet['tzcode'], $opencodes, $opencode);
            
            // 以下是原来的 K3 逻辑（保留用于对比）
            // switch ($bet['playid']) {
            //     case 'k3hzbig': // 大
            //         $isWin = ($sum > 10 && $bet['tzcode'] == '大');
            //         break;
            //     case 'k3hzsmall': // 小
            //         $isWin = ($sum <= 10 && $bet['tzcode'] == '小');
            //         break;
            //     case 'k3hzodd': // 单
            //         $isWin = ($sum % 2 != 0 && $bet['tzcode'] == '单');
            //         break;
            //     case 'k3hzeven': // 双
            //         $isWin = ($sum % 2 == 0 && $bet['tzcode'] == '双');
            //         break;
            // }
            
            if ($isWin) {
                // 中奖
                $rate = floatval($bet['mode']); // 赔率
                $winAmount = floatval($bet['amount']) * $rate; // 中奖金额 = 投注金额 × 赔率
                
                // 更新投注记录
                $updateStmt = $pdo->prepare("
                    UPDATE {$prefix}touzhu 
                    SET isdraw = 1, okamount = :okamount, okcount = 1, opencode = :opencode
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':okamount' => $winAmount,
                    ':opencode' => $opencode,
                    ':id' => $bet['id']
                ]);
                
                // 更新用户余额
                $userStmt = $pdo->prepare("SELECT balance FROM {$prefix}member WHERE id = :uid");
                $userStmt->execute([':uid' => $bet['uid']]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $oldBalance = floatval($user['balance']);
                    $newBalance = $oldBalance + $winAmount;
                    
                    $balanceStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :balance WHERE id = :uid");
                    $balanceStmt->execute([
                        ':balance' => $newBalance,
                        ':uid' => $bet['uid']
                    ]);
                    
                    // 插入账变记录
                    // 生成交易单号（限制长度避免超出数据库字段限制）
                    $trano = date('YmdHis') . rand(100, 999); // 格式：20240109123045123 (17位)
                    
                    $fudStmt = $pdo->prepare("
                        INSERT INTO {$prefix}fuddetail (
                            uid, username, type, typename, amount, 
                            amountbefor, amountafter, remark, oddtime, trano
                        ) VALUES (
                            :uid, :username, :type, :typename, :amount,
                            :amountbefor, :amountafter, :remark, :oddtime, :trano
                        )
                    ");
                    
                    // 将大小单双转换为普货精品一件多件
                    $remarkMap = [
                        '大' => '普货',
                        '小' => '精品',
                        '单' => '一件',
                        '双' => '多件'
                    ];
                    $tzcode = $bet['tzcode'];
                    $displayCode = isset($remarkMap[$tzcode]) ? $remarkMap[$tzcode] : $tzcode;
                    
                    $fudStmt->execute([
                        ':uid' => $bet['uid'],
                        ':username' => $bet['username'],
                        ':type' => 'prize',
                        ':typename' => '中奖',
                        ':amount' => $winAmount,
                        ':amountbefor' => $oldBalance,
                        ':amountafter' => $newBalance,
                        ':remark' => "期号{$expect} {$displayCode} 中奖",
                        ':oddtime' => time(),
                        ':trano' => $trano
                    ]);
                }
            } else {
                // 未中奖
                $updateStmt = $pdo->prepare("
                    UPDATE {$prefix}touzhu 
                    SET isdraw = -1, okamount = 0, okcount = 0, opencode = :opencode
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':opencode' => $opencode,
                    ':id' => $bet['id']
                ]);
            }
        }
        
        // 提交事务
        $pdo->commit();
        
        $settledCount += count($bets);
        $settledPeriods[] = [
            'cpname' => $cpname,
            'expect' => $expect,
            'count' => count($bets),
            'opencode' => $opencode
        ];
    }
    
    Database::success('结算完成', [
        'settled_count' => $settledCount,
        'periods' => $settledPeriods
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    Database::error('结算失败：' . $e->getMessage());
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    Database::error('操作失败：' . $e->getMessage());
}

/**
 * 判断是否中奖
 * @param string $cpname 彩票名称
 * @param string $playid 玩法ID
 * @param string $tzcode 投注号码
 * @param array $opencodes 开奖号码数组
 * @param string $opencode 开奖号码字符串
 * @return bool
 */
function checkWin($cpname, $playid, $tzcode, $opencodes, $opencode) {
    // 根据彩票名称判断彩票类型
    $lotteryType = getLotteryType($cpname);
    
    switch ($lotteryType) {
        case 'k3':
            return checkK3Win($playid, $tzcode, $opencodes);
        case 'ssc':
            return checkSSCWin($playid, $tzcode, $opencodes);
        case 'fc3d':
            return check3DWin($playid, $tzcode, $opencodes);
        case 'pk10':
            return checkPK10Win($playid, $tzcode, $opencodes);
        case '11x5':
            return check11x5Win($playid, $tzcode, $opencodes);
        default:
            // 默认使用 K3 逻辑
            return checkK3Win($playid, $tzcode, $opencodes);
    }
}

/**
 * 根据彩票名称判断彩票类型
 */
function getLotteryType($cpname) {
    if (strpos($cpname, 'k3') !== false) {
        return 'k3';
    } elseif (strpos($cpname, 'ssc') !== false) {
        return 'ssc';
    } elseif (strpos($cpname, '3d') !== false || strpos($cpname, 'fc3d') !== false) {
        return 'fc3d';
    } elseif (strpos($cpname, 'pk10') !== false) {
        return 'pk10';
    } elseif (strpos($cpname, '11x5') !== false) {
        return '11x5';
    }
    return 'k3'; // 默认
}

/**
 * K3类彩票中奖判断
 */
function checkK3Win($playid, $tzcode, $opencodes) {
    $sum = array_sum($opencodes);
    
    switch ($playid) {
        case 'k3hzbig': // 大
            return ($sum > 10 && $tzcode == '大');
        case 'k3hzsmall': // 小
            return ($sum <= 10 && $tzcode == '小');
        case 'k3hzodd': // 单
            return ($sum % 2 != 0 && $tzcode == '单');
        case 'k3hzeven': // 双
            return ($sum % 2 == 0 && $tzcode == '双');
        default:
            return false;
    }
}

/**
 * 时时彩类彩票中奖判断
 */
function checkSSCWin($playid, $tzcode, $opencodes) {
    // 时时彩玩法：一字定位、二字定位、三字定位、大小单双等
    $sum = array_sum($opencodes);
    
    if (strpos($playid, 'big') !== false) {
        return $sum > 22; // 总和大
    } elseif (strpos($playid, 'small') !== false) {
        return $sum <= 22; // 总和小
    } elseif (strpos($playid, 'odd') !== false) {
        return $sum % 2 != 0; // 总和单
    } elseif (strpos($playid, 'even') !== false) {
        return $sum % 2 == 0; // 总和双
    }
    
    // 定位玩法：检查投注号码是否在对应位置
    if (preg_match('/^(\d+)_(\d+)$/', $tzcode, $matches)) {
        $position = intval($matches[1]); // 位置（0-4）
        $number = intval($matches[2]); // 号码（0-9）
        if (isset($opencodes[$position])) {
            return $opencodes[$position] == $number;
        }
    }
    
    return false;
}

/**
 * 福彩3D类彩票中奖判断
 */
function check3DWin($playid, $tzcode, $opencodes) {
    // 3D玩法：直选、组选3、组选6、大小单双等
    if ($playid == 'direct') {
        // 直选：号码完全一致
        $tzArr = explode(',', $tzcode);
        return $tzArr == $opencodes;
    }
    
    $sum = array_sum($opencodes);
    
    if (strpos($playid, 'big') !== false) {
        return $sum > 13;
    } elseif (strpos($playid, 'small') !== false) {
        return $sum <= 13;
    } elseif (strpos($playid, 'odd') !== false) {
        return $sum % 2 != 0;
    } elseif (strpos($playid, 'even') !== false) {
        return $sum % 2 == 0;
    }
    
    return false;
}

/**
 * PK10类彩票中奖判断
 */
function checkPK10Win($playid, $tzcode, $opencodes) {
    // PK10玩法：冠亚军、前三名、大小单双等
    if (empty($opencodes)) {
        return false;
    }
    
    $first = intval($opencodes[0]); // 冠军
    $second = isset($opencodes[1]) ? intval($opencodes[1]) : 0; // 亚军
    $sum = $first + $second;
    
    if (strpos($playid, 'big') !== false) {
        return $sum > 11; // 冠亚和大
    } elseif (strpos($playid, 'small') !== false) {
        return $sum <= 11; // 冠亚和小
    } elseif (strpos($playid, 'odd') !== false) {
        return $sum % 2 != 0; // 冠亚和单
    } elseif (strpos($playid, 'even') !== false) {
        return $sum % 2 == 0; // 冠亚和双
    }
    
    // 定位玩法
    if (preg_match('/^(\d+)_(\d+)$/', $tzcode, $matches)) {
        $position = intval($matches[1]);
        $number = intval($matches[2]);
        if (isset($opencodes[$position])) {
            return $opencodes[$position] == $number;
        }
    }
    
    return false;
}

/**
 * 11选5类彩票中奖判断
 */
function check11x5Win($playid, $tzcode, $opencodes) {
    // 11选5玩法：前一、前二、前三、大小单双等
    if (empty($opencodes)) {
        return false;
    }
    
    $sum = array_sum($opencodes);
    
    if (strpos($playid, 'big') !== false) {
        return $sum > 28; // 总和大
    } elseif (strpos($playid, 'small') !== false) {
        return $sum <= 28; // 总和小
    } elseif (strpos($playid, 'odd') !== false) {
        return $sum % 2 != 0; // 总和单
    } elseif (strpos($playid, 'even') !== false) {
        return $sum % 2 == 0; // 总和双
    }
    
    // 直选玩法
    $tzArr = explode(',', $tzcode);
    sort($tzArr);
    $openArr = $opencodes;
    sort($openArr);
    return $tzArr == $openArr;
}

