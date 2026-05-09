<?php
namespace app\common\model;

use think\Model;

class WxLoginSession extends Model
{
    protected $table = 'wx_login_sessions';
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 创建登录会话
     */
    public static function createSession(): self
    {
        // 清理过期会话
        self::where('expired_at', '<', date('Y-m-d H:i:s'))->delete();

        return self::create([
            'session_key' => self::generateKey(),
            'status'      => 'pending',
            'expired_at'  => date('Y-m-d H:i:s', time() + 300), // 5分钟
        ]);
    }

    /**
     * 生成动态码（8位大写字母数字）
     */
    private static function generateKey(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $key = '';
        for ($i = 0; $i < 8; $i++) {
            $key .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $key;
    }
}
