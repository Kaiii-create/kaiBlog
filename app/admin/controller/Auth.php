<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Admin;
use app\common\model\AdminRole;
use app\common\model\Role;
use app\common\model\Permission;
use app\common\util\Jwt;

class Auth extends BaseController
{
    use ApiResponse;

    /**
     * 管理员登录（单点登录）
     * POST /admin/auth/login
     * Body: { username, password, captcha_key, captcha }
     */
    public function login()
    {
        $data = $this->request->post();

        // 验证码校验
        if (!\app\api\controller\CaptchaController::verify($data['captcha_key'] ?? '', $data['captcha'] ?? '')) {
            return $this->error('验证码错误或已过期');
        }

        if (empty($data['username']) || empty($data['password'])) {
            return $this->error('请输入用户名和密码');
        }

        $admin = Admin::where('username', $data['username'])->find();
        if (!$admin || sha1($data['password']) !== $admin->password) {
            return $this->error('用户名或密码错误');
        }

        if ($admin->status !== 1) {
            return $this->error('账号已被禁用');
        }

        $admin->last_login_ip = $this->request->ip();
        $admin->last_login_at = date('Y-m-d H:i:s');

        $token = Jwt::encode([
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
        ]);

        // 单点登录：保存 token，之前登录的会失效
        $admin->token = $token;
        $admin->save();

        return $this->success([
            'token' => $token,
            'admin' => $admin,
        ], '登录成功');
    }

    /**
     * 获取管理员信息（含角色、权限、菜单）
     * GET /api/admin/auth/profile
     */
    public function profile()
    {
        $admin = Admin::find($this->request->admin_id);
        if (!$admin) return $this->error('管理员不存在');

        $roleIds = AdminRole::where('admin_id', $admin->id)->column('role_id');
        $roles = Role::whereIn('id', $roleIds)->select();

        $isSuperAdmin = false;
        foreach ($roles as $role) {
            if ($role->name === '超级管理员' || $role->id === 1) {
                $isSuperAdmin = true;
                break;
            }
        }

        // 获取所有权限（扁平）
        $allPermissions = [];
        // 菜单树（只含 type=1 的菜单）
        $menuTree = [];
        // 按钮权限 slug 列表（用于前端控制按钮显示）
        $buttonPermissions = [];

        if ($isSuperAdmin) {
            // 超级管理员：全部权限
            $allPermissions = Permission::order('sort', 'asc')->select()->toArray();
        } else {
            $permIds = \app\common\model\RolePermission::whereIn('role_id', $roleIds)->column('permission_id');
            if (!empty($permIds)) {
                $allPermissions = Permission::whereIn('id', $permIds)->order('sort', 'asc')->select()->toArray();
            }
        }

        // 分离菜单和按钮
        $menuItems = [];
        foreach ($allPermissions as $perm) {
            if ($perm['type'] == 1) {
                $menuItems[] = $perm;
            } else {
                $buttonPermissions[] = $perm['slug'];
            }
        }

        // 构建菜单树（仅一级和二级）
        $menuTree = $this->buildMenuTree($menuItems, 0);

        return $this->success([
            'admin' => $admin,
            'roles' => $roles,
            'is_super_admin' => $isSuperAdmin,
            'permissions' => $allPermissions,
            'button_permissions' => $buttonPermissions,
            'menus' => $menuTree,
        ]);
    }

    /**
     * 构建菜单树
     */
    private function buildMenuTree(array $items, int $parentId): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($items, $item['id']);
                $node = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'icon' => $item['icon'],
                    'route_path' => $item['route_path'],
                ];
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }
}
