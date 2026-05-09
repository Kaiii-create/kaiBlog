<?php
namespace app\api\middleware;

use think\Response;
use app\common\util\Jwt;

class ApiAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization', '');
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (empty($token)) {
            return json(['code' => 401, 'message' => '请先登录', 'data' => null]);
        }

        $payload = Jwt::decode($token);
        if (!$payload || !isset($payload['user_id'])) {
            return json(['code' => 401, 'message' => 'Token无效或已过期', 'data' => null]);
        }

        // 将用户信息注入请求
        $request->user_id = $payload['user_id'];
        $request->username = $payload['username'] ?? '';

        return $next($request);
    }
}
