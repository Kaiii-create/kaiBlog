<?php
namespace app\admin\middleware;

use app\common\model\Admin;
use app\common\model\Role;
use app\common\model\Permission;
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
        $method = strtoupper($request->method());

        // 白名单跳过
        foreach ($this->except as $except) {
            if (strpos($path, $except) !== false) {
                return $next($request);
            }
        }

        // 获取角色拥有的权限ID列表
        $permIds = RolePermission::whereIn('role_id', $roleIds)
            ->where('permission_id', '>', 0)
            ->column('permission_id');

        if (empty($permIds)) {
            return json(['code' => 403, 'message' => '无操作权限']);
        }

        // 查询这些权限中配置了 api_path 的项
        $permissions = Permission::whereIn('id', $permIds)
            ->whereNotNull('api_path')
            ->where('api_path', '<>', '')
            ->field('api_path, method')
            ->select();

        if ($permissions->isEmpty()) {
            return json(['code' => 403, 'message' => '无操作权限']);
        }

        // 精确匹配 + 模式匹配（将路径中的数字ID替换为:id）
        $matched = false;
        foreach ($permissions as $perm) {
            $permPath = $perm->api_path;
            $permMethod = strtoupper($perm->method);

            // 方法不匹配则跳过
            if ($permMethod !== $method) continue;

            // 精确匹配
            if ($permPath === $path) {
                $matched = true;
                break;
            }

            // 模式匹配：将路径中的数字ID替换为 :id
            // 例如 /admin/articles/123 -> /admin/articles/:id
            $pattern = preg_replace('/\/\d+/', '/:id', $path);
            if ($permPath === $pattern) {
                $matched = true;
                break;
            }

            // 更深层模式匹配：支持多级数字参数
            // 例如 /admin/articles/123/comments -> /admin/articles/:id/comments 或 /admin/articles/:id/comments/:id
            $deepPattern = preg_replace('/\/\d+(\/|$)/', '/:id$1', $path);
            if ($permPath === $deepPattern) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            return json(['code' => 403, 'message' => '无操作权限']);
        }

        return $next($request);
    }
}
