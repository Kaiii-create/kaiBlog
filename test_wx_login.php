<?php
require __DIR__ . '/vendor/autoload.php';
$env = [];
foreach (file(__DIR__ . '/.env') as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#' || $line[0] === '[') continue;
    if (strpos($line, '=') !== false) {
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}
$pdo = new PDO('mysql:host='.$env['HOSTNAME'].';port='.$env['HOSTPORT'].';dbname='.$env['DATABASE'], $env['USERNAME'], $env['PASSWORD']);

echo "=== 第一次登录测试 ===\n";

// 1. 创建登录会话
$sessionKey = 'TEST' . strtoupper(substr(md5(uniqid()), 0, 4));
$pdo->exec("INSERT INTO wx_login_sessions (session_key, status, expired_at, created_at) VALUES ('$sessionKey', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())");
echo "1. 创建会话: $sessionKey\n";

// 2. 模拟微信用户确认登录
$openid = 'test_openid_' . time();
// 创建微信用户
$pdo->exec("INSERT INTO wx_users (openid, subscribe, subscribe_at, login_count) VALUES ('$openid', 1, NOW(), 0) ON DUPLICATE KEY UPDATE subscribe=1, subscribe_at=NOW()");
// 创建本地用户
$pdo->exec("INSERT INTO users (username, nickname, password, status) VALUES ('wx_test_user', '微信测试用户', SHA1('test'), 1)");
$userId = $pdo->lastInsertId();
echo "2. 创建本地用户: ID=$userId\n";

// 绑定微信用户
$pdo->exec("UPDATE wx_users SET user_id=$userId, last_login_at=NOW(), login_count=login_count+1 WHERE openid='$openid'");
// 确认登录会话
$pdo->exec("UPDATE wx_login_sessions SET status='confirmed', user_id=$userId, openid='$openid' WHERE session_key='$sessionKey'");
echo "3. 登录会话已确认\n";

echo "\n=== 第二次登录测试（同一微信用户）===\n";

// 模拟第二次登录
$sessionKey2 = 'TEST' . strtoupper(substr(md5(uniqid()), 0, 4));
$pdo->exec("INSERT INTO wx_login_sessions (session_key, status, expired_at, created_at) VALUES ('$sessionKey2', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())");
echo "1. 创建新会话: $sessionKey2\n";

// 同一个 openid 再次确认
$pdo->exec("UPDATE wx_users SET last_login_at=NOW(), login_count=login_count+1 WHERE openid='$openid'");
$pdo->exec("UPDATE wx_login_sessions SET status='confirmed', user_id=$userId, openid='$openid' WHERE session_key='$sessionKey2'");
echo "2. 同一微信用户再次确认\n";

// 验证：不会创建新用户，复用已有用户
$stmt = $pdo->query("SELECT id, username, nickname FROM users WHERE id=$userId");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "3. 登录用户: ID={$user['id']} username={$user['username']} nickname={$user['nickname']}\n";

$stmt = $pdo->query("SELECT openid, user_id, login_count FROM wx_users WHERE openid='$openid'");
$wx = $stmt->fetch(PDO::FETCH_ASSOC);
echo "4. 微信绑定: openid={$wx['openid']} user_id={$wx['user_id']} login_count={$wx['login_count']}\n";

// 清理测试数据
echo "\n=== 清理测试数据 ===\n";
$pdo->exec("DELETE FROM wx_login_sessions WHERE session_key IN ('$sessionKey', '$sessionKey2')");
$pdo->exec("DELETE FROM wx_users WHERE openid='$openid'");
$pdo->exec("DELETE FROM users WHERE id=$userId");
echo "Done.\n";
