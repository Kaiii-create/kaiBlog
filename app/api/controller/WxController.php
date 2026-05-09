<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\service\WxService;
use app\common\model\WxLoginSession;
use app\common\model\User;
use app\common\util\Jwt;

class WxController extends BaseController
{
    use ApiResponse;

    /**
     * 微信消息回调（GET验证 + POST接收消息）
     * GET/POST /api/wx/event
     */
    public function event()
    {
        if ($this->request->isGet()) {
            // 服务器验证
            $signature = $this->request->get('signature', '');
            $timestamp = $this->request->get('timestamp', '');
            $nonce = $this->request->get('nonce', '');
            $echostr = $this->request->get('echostr', '');

            if (WxService::verifySignature($signature, $timestamp, $nonce)) {
                // 直接输出 echostr，绕过 ThinkPHP 响应机制（避免追加 debug trace）
                header('Content-Type: text/plain; charset=utf-8');
                echo $echostr;
                exit;
            }
            header('Content-Type: text/plain');
            http_response_code(403);
            echo 'invalid';
            exit;
        }

        // POST: 接收消息
        $xml = $this->request->getContent();
        $msg = WxService::parseMessage($xml);
        if (!$msg) {
            header('Content-Type: text/plain');
            echo 'success';
            exit;
        }

        $reply = WxService::handleMessage($msg);
        header('Content-Type: text/xml; charset=utf-8');
        echo $reply;
        exit;
    }

    /**
     * 生成微信登录动态码
     * GET /api/auth/wx-login
     *
     * 返回: { key: "ABCD1234", qr_url: "https://...", expire: 300 }
     */
    public function wxLogin()
    {
        $session = WxLoginSession::createSession();
        $key = $session->session_key;

        // 尝试生成微信带参数二维码
        $qrResult = WxService::createQrCode($key);

        if ($qrResult) {
            return $this->success([
                'key'     => $key,
                'qr_url'  => $qrResult['url'],
                'qr_type' => 'wechat',  // 微信原生二维码
                'expire'  => $qrResult['expire'],
                'tip'     => '请使用微信扫描二维码，关注公众号后自动登录',
            ]);
        }

        // 如果获取微信二维码失败（access_token问题），返回动态码让用户手动发送
        return $this->success([
            'key'     => $key,
            'qr_url'  => null,
            'qr_type' => 'manual',  // 手动模式
            'expire'  => 300,
            'tip'     => "请关注公众号「KaiBlog」后，发送以下动态码：{$key}",
        ]);
    }

    /**
     * 前端轮询检查登录状态
     * GET /api/auth/wx-check?key=ABCD1234
     */
    public function wxCheck()
    {
        $key = $this->request->get('key', '');
        if (empty($key)) return $this->error('缺少 key 参数');

        $session = WxLoginSession::where('session_key', $key)->find();

        if (!$session) {
            return $this->error('无效的登录码', 400);
        }

        // 检查过期
        if ($session->expired_at < date('Y-m-d H:i:s')) {
            $session->status = 'expired';
            $session->save();
            return $this->error('登录码已过期，请重新获取', 410);
        }

        // 未确认，继续等待
        if ($session->status === 'pending') {
            return $this->success(['status' => 'pending'], '等待扫码...');
        }

        // 已确认，生成 token
        if ($session->status === 'confirmed' && $session->user_id) {
            $user = User::find($session->user_id);
            if (!$user) return $this->error('用户不存在', 404);

            // 生成 JWT
            $token = Jwt::encode([
                'user_id'  => $user->id,
                'username' => $user->username,
            ]);

            // 标记会话已使用
            $session->status = 'used';
            $session->save();

            return $this->success([
                'status' => 'confirmed',
                'token'  => $token,
                'user'   => $user,
            ], '登录成功');
        }

        return $this->success(['status' => $session->status]);
    }

    /**
     * 获取微信登录配置信息（给前端用）
     * GET /api/wx/config
     */
    public function config()
    {
        return $this->success([
            'enabled' => true,
            'app_id'  => 'wxed57e35f1d6a2e91',
            'name'    => 'KaiBlog',
        ]);
    }
}
