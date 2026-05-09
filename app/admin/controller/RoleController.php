<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Role;
use app\common\model\RolePermission;

class RoleController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = Role::withCount('admins')->order('id', 'asc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $role = Role::with('permissions')->find($id);
        if (!$role) return $this->error('角色不存在', 404);
        return $this->success($role);
    }

    public function save()
    {
        $data = $this->request->post();
        if (empty($data['name'])) return $this->error('角色名称不能为空');

        $exist = Role::where('name', $data['name'])->find();
        if ($exist) return $this->error('角色名已存在');

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 1,
        ]);

        // 同步权限
        if (!empty($data['permission_ids'])) {
            $this->syncPermissions($role->id, $data['permission_ids']);
        }

        return $this->success($role, '创建成功');
    }

    public function update($id)
    {
        $role = Role::find($id);
        if (!$role) return $this->error('角色不存在', 404);

        $data = $this->request->put();
        if (!empty($data['name'])) {
            $exist = Role::where('name', $data['name'])->where('id', '<>', $id)->find();
            if ($exist) return $this->error('角色名已存在');
        }

        $role->save([
            'name' => $data['name'] ?? $role->name,
            'description' => $data['description'] ?? $role->description,
            'status' => $data['status'] ?? $role->status,
        ]);

        return $this->success($role, '更新成功');
    }

    public function delete($id)
    {
        if ($id == 1) return $this->error('不能删除超级管理员角色');
        $role = Role::find($id);
        if (!$role) return $this->error('角色不存在', 404);
        Role::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        RolePermission::where('role_id', $id)->delete();
        return $this->success(null, '删除成功');
    }

    /**
     * 分配权限
     * PUT /api/admin/roles/:id/permissions
     */
    public function assignPermissions($id)
    {
        $role = Role::find($id);
        if (!$role) return $this->error('角色不存在', 404);

        $data = $this->request->put();
        if (!isset($data['permission_ids'])) return $this->error('缺少 permission_ids');

        $this->syncPermissions($id, $data['permission_ids']);
        return $this->success(null, '权限分配成功');
    }

    private function syncPermissions(int $roleId, array $permissionIds)
    {
        RolePermission::where('role_id', $roleId)->delete();
        foreach ($permissionIds as $pid) {
            RolePermission::create(['role_id' => $roleId, 'permission_id' => $pid]);
        }
    }
}
