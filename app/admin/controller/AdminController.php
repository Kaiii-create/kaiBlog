<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Admin;
use app\common\model\AdminRole;

class AdminController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $keyword = $this->request->get('keyword');

        $query = Admin::with('roles')->order('id', 'asc');
        if ($keyword) {
            $query->where('username|nickname', 'like', "%{$keyword}%");
        }
        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $admin = Admin::with('roles')->find($id);
        if (!$admin) return $this->error('管理员不存在', 404);
        return $this->success($admin);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = [
            'username' => 'require|alphaDash|length:3,50|unique:admins',
            'password' => 'require|length:6,32',
        ];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $admin = Admin::create([
            'username' => $data['username'],
            'password' => sha1($data['password']),
            'nickname' => $data['nickname'] ?? $data['username'],
            'role' => $data['role'] ?? 'admin',
            'status' => $data['status'] ?? 1,
        ]);

        // 分配角色
        if (!empty($data['role_ids'])) {
            foreach ($data['role_ids'] as $roleId) {
                AdminRole::create(['admin_id' => $admin->id, 'role_id' => $roleId]);
            }
        }

        return $this->success($admin, '创建成功');
    }

    public function update($id)
    {
        $admin = Admin::find($id);
        if (!$admin) return $this->error('管理员不存在', 404);

        $data = $this->request->put();
        $updateData = [];

        if (!empty($data['nickname'])) $updateData['nickname'] = $data['nickname'];
        if (!empty($data['password'])) $updateData['password'] = sha1($data['password']);
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        if (!empty($data['role'])) $updateData['role'] = $data['role'];

        if (!empty($updateData)) {
            $admin->save($updateData);
        }

        // 更新角色
        if (isset($data['role_ids'])) {
            AdminRole::where('admin_id', $id)->delete();
            foreach ($data['role_ids'] as $roleId) {
                AdminRole::create(['admin_id' => $id, 'role_id' => $roleId]);
            }
        }

        return $this->success($admin, '更新成功');
    }

    public function delete($id)
    {
        if ($id == 1) return $this->error('不能删除超级管理员');
        $admin = Admin::find($id);
        if (!$admin) return $this->error('管理员不存在', 404);
        Admin::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        AdminRole::where('admin_id', $id)->delete();
        return $this->success(null, '删除成功');
    }
}
