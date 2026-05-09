<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Redirect;

class RedirectController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = Redirect::order('id', 'desc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function save()
    {
        $data = $this->request->post();
        if (empty($data['from_path']) || empty($data['to_path'])) {
            return $this->error('原路径和目标路径不能为空');
        }
        $redirect = Redirect::create([
            'from_path' => $data['from_path'],
            'to_path' => $data['to_path'],
            'redirect_type' => $data['type'] ?? 301,
            'status' => $data['status'] ?? 1,
        ]);
        return $this->success($redirect, '创建成功');
    }

    public function update($id)
    {
        $redirect = Redirect::find($id);
        if (!$redirect) return $this->error('重定向不存在', 404);
        $redirect->save($this->request->put());
        return $this->success($redirect, '更新成功');
    }

    public function delete($id)
    {
        $redirect = Redirect::find($id);
        if (!$redirect) return $this->error('重定向不存在', 404);
        $redirect->delete();
        return $this->success(null, '删除成功');
    }
}
