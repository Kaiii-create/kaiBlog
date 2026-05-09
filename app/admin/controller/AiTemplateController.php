<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\AiTemplate;

class AiTemplateController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $query = AiTemplate::order('id', 'asc');
        return $this->success($query->select());
    }

    public function read($id)
    {
        $item = AiTemplate::find($id);
        if (!$item) return $this->error('不存在', 404);
        return $this->success($item);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['name' => 'require|max:100', 'prompt' => 'require'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $item = AiTemplate::create($data);
        return $this->success($item, '创建成功');
    }

    public function update($id)
    {
        $item = AiTemplate::find($id);
        if (!$item) return $this->error('不存在', 404);
        $item->save($this->request->put());
        return $this->success($item, '更新成功');
    }

    public function delete($id)
    {
        $item = AiTemplate::find($id);
        if (!$item) return $this->error('不存在', 404);
        $item->delete();
        return $this->success(null, '删除成功');
    }
}
