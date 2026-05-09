<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Tag;

class TagController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = Tag::order('id', 'desc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function save()
    {
        $data = $this->request->post();
        if (empty($data['name'])) return $this->error('标签名不能为空');

        $exist = Tag::where('name', $data['name'])->find();
        if ($exist) return $this->error('标签已存在');

        $tag = Tag::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $data['name'],
            'color' => $data['color'] ?? null,
        ]);
        return $this->success($tag, '创建成功');
    }

    public function update($id)
    {
        $tag = Tag::find($id);
        if (!$tag) return $this->error('标签不存在', 404);
        $tag->save($this->request->put());
        return $this->success($tag, '更新成功');
    }

    public function delete($id)
    {
        $tag = Tag::find($id);
        if (!$tag) return $this->error('标签不存在', 404);
        $tag->delete();
        return $this->success(null, '删除成功');
    }
}
