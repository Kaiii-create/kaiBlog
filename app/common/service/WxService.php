<?php
namespace app\common\service;

class WxService
{
    // 公众号配置
    private static $appId     = 'wxed57e35f1d6a2e91';
    private static $appSecret = '34d9961c89ce87c40bcc3a0f0d062bbb';
    private static $token     = 'kaiBlog2026';

    /**
     * 验证微信服务器签名（GET 请求验证）
     */
    public static function verifySignature(string $signature, string $timestamp, string $nonce): bool
    {
        $tmpArr = [self::$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode($tmpArr));
        return $tmpStr === $signature;
    }

    /**
     * 解析微信推送的 XML 消息
     */
    public static function parseMessage(string $xml): ?array
    {
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$data) return null;

        $result = [];
        foreach ($data as $key => $value) {
            $result[(string)$key] = (string)$value;
        }
        return $result;
    }

    /**
     * 生成回复 XML
     */
    public static function buildReply(string $toUser, string $fromUser, string $content, string $msgType = 'text'): string
    {
        $time = time();
        if ($msgType === 'text') {
            return <<<XML
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$content}]]></Content>
</xml>
XML;
        }
        return 'success';
    }

    /**
     * 处理微信推送的消息事件
     * @return string 回复XML
     */
    public static function handleMessage(array $msg): string
    {
        $msgType = $msg['MsgType'] ?? '';
        $fromUser = $msg['FromUserName'] ?? '';
        $toUser = $msg['ToUserName'] ?? '';

        // 关注/取关事件
        if ($msgType === 'event') {
            $event = $msg['Event'] ?? '';
            return self::handleEvent($event, $fromUser, $toUser, $msg);
        }

        // 文本消息（动态码登录）
        if ($msgType === 'text') {
            $content = trim($msg['Content'] ?? '');
            return self::handleTextMessage($content, $fromUser, $toUser);
        }

        // 其他消息不回复
        return 'success';
    }

    /**
     * 处理事件
     */
    private static function handleEvent(string $event, string $openid, string $toUser, array $msg): string
    {
        $wxUser = \app\common\model\WxUser::where('openid', $openid)->find();

        switch ($event) {
            case 'subscribe':
                // 关注事件
                if (!$wxUser) {
                    $wxUser = \app\common\model\WxUser::create([
                        'openid'       => $openid,
                        'subscribe'    => 1,
                        'subscribe_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $wxUser->subscribe = 1;
                    $wxUser->subscribe_at = date('Y-m-d H:i:s');
                    $wxUser->unsubscribe_at = null;
                    $wxUser->save();
                }

                // 如果是扫码关注（带场景值），尝试自动登录
                $eventKey = $msg['EventKey'] ?? '';
                if (strpos($eventKey, 'qrscene_') === 0) {
                    $sessionKey = substr($eventKey, 8);
                    $result = self::tryConfirmLogin($sessionKey, $openid);
                    if ($result) {
                        return self::buildReply($openid, $toUser,
                            "🎉 欢迎关注！\n\n✅ 登录成功！\n请返回浏览器查看。\n\n—— KaiBlog 技术博客");
                    }
                }

                return self::buildReply($openid, $toUser,
                    "👋 欢迎关注 KaiBlog！\n\n如需登录博客，请在登录页获取动态码，然后发送给我即可。\n\n—— 技术博客 · 每日更新");

            case 'unsubscribe':
                // 取关事件
                if ($wxUser) {
                    $wxUser->subscribe = 0;
                    $wxUser->unsubscribe_at = date('Y-m-d H:i:s');
                    $wxUser->save();
                }
                return 'success';

            case 'SCAN':
                // 已关注用户扫码（带场景值）
                $eventKey = $msg['EventKey'] ?? '';
                $result = self::tryConfirmLogin($eventKey, $openid);
                if ($result) {
                    return self::buildReply($openid, $toUser,
                        "✅ 登录成功！\n请返回浏览器查看。\n\n—— KaiBlog 技术博客");
                }
                return self::buildReply($openid, $toUser,
                    "👋 欢迎回来！\n如需登录，请在登录页获取动态码后发送给我。");

            default:
                return 'success';
        }
    }

    /**
     * 处理文本消息（动态码验证）
     */
    private static function handleTextMessage(string $content, string $openid, string $toUser): string
    {
        // 去除可能的空格和引号
        $content = trim($content, " \t\n\r\0\x0B\"'");

        // 检查是否是动态码（UUID格式或8位字母数字）
        $session = \app\common\model\WxLoginSession::where('session_key', $content)
            ->where('status', 'pending')
            ->where('expired_at', '>', date('Y-m-d H:i:s'))
            ->find();

        if (!$session) {
            // 不是动态码，当普通消息处理
            return self::buildReply($openid, $toUser,
                "🤔 未识别的指令。\n\n如需登录博客，请在登录页获取动态码后发送给我。");
        }

        $result = self::tryConfirmLogin($content, $openid);
        if ($result) {
            return self::buildReply($openid, $toUser,
                "✅ 登录成功！\n请返回浏览器查看。\n\n—— KaiBlog 技术博客");
        }

        return self::buildReply($openid, $toUser,
            "❌ 登录验证失败，请重新获取动态码。");
    }

    /**
     * 尝试确认登录
     */
    private static function tryConfirmLogin(string $sessionKey, string $openid): bool
    {
        $session = \app\common\model\WxLoginSession::where('session_key', $sessionKey)
            ->where('status', 'pending')
            ->where('expired_at', '>', date('Y-m-d H:i:s'))
            ->find();

        if (!$session) return false;

        // 查找或创建微信用户
        $wxUser = \app\common\model\WxUser::where('openid', $openid)->find();
        if (!$wxUser) {
            $wxUser = \app\common\model\WxUser::create([
                'openid'       => $openid,
                'subscribe'    => 1,
                'subscribe_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 查找或创建本地用户
        $userId = $wxUser->user_id;
        if (!$userId) {
            // 首次登录，创建本地用户
            $user = \app\common\model\User::create([
                'username' => 'wx_' . substr($openid, -8),
                'nickname' => '微信用户',
                'password' => sha1(uniqid()), // 随机密码
            ]);
            $userId = $user->id;
            $wxUser->user_id = $userId;
        }

        // 更新微信用户信息
        $wxUser->last_login_at = date('Y-m-d H:i:s');
        $wxUser->login_count += 1;
        $wxUser->save();

        // 确认登录会话
        $session->status = 'confirmed';
        $session->user_id = $userId;
        $session->openid = $openid;
        $session->save();

        return true;
    }

    /**
     * 获取 access_token（用于生成带参数的二维码）
     * 用数据库缓存，避免文件缓存目录问题
     */
    public static function getAccessToken(): ?string
    {
        $config = \app\common\model\SystemConfig::where('config_key', 'wx_access_token')->find();
        if ($config && $config->updated_at > date('Y-m-d H:i:s', time() - 7000)) {
            return $config->config_value;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . self::$appId . "&secret=" . self::$appSecret;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            // 存入数据库
            if ($config) {
                $config->config_value = $data['access_token'];
                $config->save();
            } else {
                \app\common\model\SystemConfig::create([
                    'config_key'   => 'wx_access_token',
                    'config_value' => $data['access_token'],
                    'config_type'  => 'string',
                    'config_group' => 'seo',
                    'description'  => '微信access_token(自动缓存)',
                    'sort'         => 99,
                ]);
            }
            return $data['access_token'];
        }

        return null;
    }

    /**
     * 创建临时带参数二维码
     * @param string $sceneValue 场景值（动态码）
     * @return array ['url' => 二维码图片URL, 'ticket' => ticket]
     */
    public static function createQrCode(string $sceneValue): ?array
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) return null;

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$accessToken}";
        $postData = json_encode([
            'expire_seconds' => 300, // 5分钟有效
            'action_name'    => 'QR_STR_SCENE',
            'action_info'    => [
                'scene' => ['scene_str' => $sceneValue],
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['ticket'])) {
            return [
                'ticket' => $data['ticket'],
                'url'    => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($data['ticket']),
                'expire' => $data['expire_seconds'] ?? 300,
            ];
        }

        return null;
    }
}
