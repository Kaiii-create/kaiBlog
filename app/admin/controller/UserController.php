<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\User;

class UserController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $keyword = $this->request->get('keyword');

        $query = User::order('id', 'desc');
        if ($keyword) {
            $query->where('username|nickname|email', 'like', "%{$keyword}%");
        }

        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $user = User::find($id);
        if (!$user) return $this->error('用户不存在', 404);
        return $this->success($user);
    }

    /**
     * 禁用/启用用户
     * PUT /api/admin/users/:id/status
     */
    public function status($id)
    {
        $user = User::find($id);
        if (!$user) return $this->error('用户不存在', 404);

        $data = $this->request->put();
        $user->status = $data['status'];
        $user->save();
        return $this->success($user, '操作成功');
    }
}
