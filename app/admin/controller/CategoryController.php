<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Category;

class CategoryController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = Category::withCount('articles')->order('sort', 'asc')->order('id', 'asc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $category = Category::withCount('articles')->find($id);
        if (!$category) return $this->error('栏目不存在', 404);
        return $this->success($category);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['name' => 'require|max:100', 'slug' => 'require|max:100|unique:categories'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $category = Category::create($data);
        return $this->success($category, '创建成功');
    }

    public function update($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->error('栏目不存在', 404);

        $data = $this->request->put();
        if (!empty($data['slug'])) {
            $exist = Category::where('slug', $data['slug'])->where('id', '<>', $id)->find();
            if ($exist) return $this->error('slug已存在');
        }
        $category->save($data);
        return $this->success($category, '更新成功');
    }

    public function delete($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->error('栏目不存在', 404);
        Category::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->success(null, '删除成功');
    }
}
