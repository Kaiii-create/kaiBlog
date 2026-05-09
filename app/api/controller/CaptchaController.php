<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\util\Captcha;
use think\facade\Db;

class CaptchaController extends BaseController
{
    use ApiResponse;

    /**
     * 生成验证码
     * GET /api/captcha
     *
     * 返回: { code: 0, data: { key: "xxx", image: "data:image/png;base64,..." } }
     * 前端登录时传 key + captcha 参数
     */
    public function index()
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        // 生成唯一 key
        $key = md5(uniqid(mt_rand(), true));

        // 存储验证码到数据库（5分钟有效）
        Db::table('captcha_codes')->insert([
            'captcha_key'  => $key,
            'captcha_code' => strtolower($result['code']),
            'expired_at'   => date('Y-m-d H:i:s', time() + 300),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // 清理过期验证码
        Db::table('captcha_codes')->where('expired_at', '<', date('Y-m-d H:i:s'))->delete();

        return $this->success([
            'key' => $key,
            'image' => $result['image'],
        ]);
    }

    /**
     * 校验验证码（内部调用，不对外暴露）
     * @param string $key 验证码 key
     * @param string $code 用户输入的验证码
     * @return bool
     */
    public static function verify(string $key, string $code): bool
    {
        if (empty($key) || empty($code)) {
            return false;
        }

        $record = Db::table('captcha_codes')
            ->where('captcha_key', $key)
            ->where('expired_at', '>', date('Y-m-d H:i:s'))
            ->find();

        if (!$record) {
            return false; // 已过期或不存在
        }

        // 使用后立即删除（一次性）
        Db::table('captcha_codes')->where('captcha_key', $key)->delete();

        // 不区分大小写比较
        return strtolower($code) === strtolower($record['captcha_code']);
    }
}
