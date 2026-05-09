<?php
namespace app\admin\middleware;

use app\common\model\Admin;
use app\common\model\RolePermission;

class CheckPermission
{
    // 不需要权限验证的路由
    protected $except = [
        'admin/auth/login',
        'admin/auth/profile',
        'admin/dashboard',
    ];

    public function handle($request, \Closure $next)
    {
        $adminId = $request->admin_id ?? 0;
        if (!$adminId) {
            return json(['code' => 401, 'message' => '请先登录']);
        }

        // 超级管理员跳过权限检查
        $admin = Admin::find($adminId);
        if (!$admin) {
            return json(['code' => 401, 'message' => '管理员不存在']);
        }

        // 获取管理员角色
        $roleIds = \app\common\model\AdminRole::where('admin_id', $adminId)->column('role_id');
        if (empty($roleIds)) {
            return json(['code' => 403, 'message' => '未分配角色']);
        }

        // 超级管理员跳过权限检查
        $isSuperAdmin = false;
        $roles = Role::whereIn('id', $roleIds)->select();
        foreach ($roles as $role) {
            if ($role->name === '超级管理员' || $role->id === 1) {
                $isSuperAdmin = true;
                break;
            }
        }
        if ($isSuperAdmin) {
            return $next($request);
        }

        // 获取当前请求的路径和方法
        $path = '/' . trim($request->pathinfo(), '/');
        $method = $request->method();

        // 白名单跳过
        foreach ($this->except as $except) {
            if (strpos($path, $except) !== false) {
                return $next($request);
            }
        }

        // 获取角色拥有的权限 slug 列表
        $permSlugs = RolePermission::whereIn('role_id', $roleIds)
            ->where('permission_id', '>', 0)
            ->column('permission_id');

        // TODO: 可以根据 api_path + method 精确匹配权限
        // 目前简化为：只要分配了任何权限就放行

        if (empty($permSlugs)) {
            return json(['code' => 403, 'message' => '无操作权限']);
        }

        return $next($request);
    }
}
