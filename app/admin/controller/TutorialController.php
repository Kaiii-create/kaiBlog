<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Tutorial;

class TutorialController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $query = Tutorial::withCount('chapters')->order('sort', 'asc')->order('id', 'desc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $tutorial = Tutorial::with('chapters')->find($id);
        if (!$tutorial) return $this->error('教程不存在', 404);
        return $this->success($tutorial);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['title' => 'require|max:200'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $data['admin_id'] = $this->request->admin_id;
        if ($data['status'] == 1 && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        $tutorial = Tutorial::create($data);
        return $this->success($tutorial, '创建成功');
    }

    public function update($id)
    {
        $tutorial = Tutorial::find($id);
        if (!$tutorial) return $this->error('教程不存在', 404);

        $data = $this->request->put();
        if (isset($data['status']) && $data['status'] == 1 && empty($tutorial->published_at)) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        $tutorial->save($data);
        return $this->success($tutorial, '更新成功');
    }

    public function delete($id)
    {
        $tutorial = Tutorial::find($id);
        if (!$tutorial) return $this->error('教程不存在', 404);
        Tutorial::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->success(null, '删除成功');
    }
}
