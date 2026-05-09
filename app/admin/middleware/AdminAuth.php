<?php
namespace app\admin\middleware;

use app\common\util\Jwt;
use app\common\model\Admin;

class AdminAuth
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
        if (!$payload || !isset($payload['admin_id'])) {
            return json(['code' => 401, 'message' => 'Token无效或已过期', 'data' => null]);
        }

        // 单点登录验证：检查 token 是否是当前有效 token
        $admin = Admin::find($payload['admin_id']);
        if (!$admin || $admin->status !== 1) {
            return json(['code' => 401, 'message' => '账号已被禁用', 'data' => null]);
        }
        if ($admin->token !== $token) {
            return json(['code' => 401, 'message' => '您的账号已在其他地方登录', 'data' => null]);
        }

        $request->admin_id = $payload['admin_id'];
        $request->admin_role = $payload['role'] ?? 'admin';

        return $next($request);
    }
}
