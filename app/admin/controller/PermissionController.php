<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Permission;

class PermissionController extends BaseController
{
    use ApiResponse;

    /**
     * 权限树形列表
     * GET /api/admin/permissions
     */
    public function index()
    {
        $tree = Permission::getTree();
        return $this->success($tree);
    }

    /**
     * 权限详情
     * GET /api/admin/permissions/:id
     */
    public function read($id)
    {
        $perm = Permission::find($id);
        if (!$perm) return $this->error('权限不存在', 404);
        return $this->success($perm);
    }

    /**
     * 新增权限
     * POST /api/admin/permissions
     */
    public function save()
    {
        $data = $this->request->post();
        if (empty($data['name']) || empty($data['slug'])) {
            return $this->error('名称和标识不能为空');
        }

        $exist = Permission::where('slug', $data['slug'])->find();
        if ($exist) return $this->error('权限标识已存在');

        $perm = Permission::create($data);
        return $this->success($perm, '创建成功');
    }

    /**
     * 更新权限
     * PUT /api/admin/permissions/:id
     */
    public function update($id)
    {
        $perm = Permission::find($id);
        if (!$perm) return $this->error('权限不存在', 404);

        $data = $this->request->put();
        if (!empty($data['slug'])) {
            $exist = Permission::where('slug', $data['slug'])->where('id', '<>', $id)->find();
            if ($exist) return $this->error('权限标识已存在');
        }

        $perm->save($data);
        return $this->success($perm, '更新成功');
    }

    /**
     * 删除权限
     * DELETE /api/admin/permissions/:id
     */
    public function delete($id)
    {
        $perm = Permission::find($id);
        if (!$perm) return $this->error('权限不存在', 404);

        // 删除子权限
        Permission::where('parent_id', $id)->delete();
        $perm->delete();
        return $this->success(null, '删除成功');
    }
}
