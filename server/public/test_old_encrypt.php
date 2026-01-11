<?php
/**
 * 使用老系统的encrypt函数测试
 */

// 定义一个简单的C函数模拟
function C($key) {
    $config = [
        'DATA_AUTH_KEY' => 'w%!)+bj$&sGX(Lp4Y@v;l#Q:i7c{MWOT-|AP"}gB',
        'AUTH_KEY' => 'w%!)+bj$&sGX(Lp4Y@v;l#Q:i7c{MWOT-|AP"}gB'
    ];
    return $config[$key] ?? '';
}

// 老系统的encrypt函数
function encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

// 老系统的decrypt函数
function decrypt($data, $key = ''){
    $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'), $data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char  .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

// 测试
$storedPassword = 'MDAwMDAwMDAwML1nmZma0JVukb51YrF2mHU';

echo "存储的密码: $storedPassword" . PHP_EOL;
echo "尝试解密: " . decrypt($storedPassword) . PHP_EOL . PHP_EOL;

$testPasswords = ['admin', 'admin123', '123456', '888888', 'a123456'];

echo "测试常见密码:" . PHP_EOL;
foreach ($testPasswords as $pass) {
    $encrypted = encrypt($pass);
    $match = ($encrypted === $storedPassword);
    echo "$pass => " . ($match ? '✓ 匹配！' : '✗ 不匹配') . " (加密: $encrypted)" . PHP_EOL;
}

