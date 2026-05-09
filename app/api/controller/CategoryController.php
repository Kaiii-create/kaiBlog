<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Category;

class CategoryController extends BaseController
{
    use ApiResponse;

    /**
     * 栏目列表（公开）
     * GET /api/web/categories
     */
    public function index()
    {
        $categories = Category::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        // 构建树形结构
        $tree = $this->buildTree($categories->toArray());
        return $this->success($tree);
    }

    /**
     * 栏目详情
     * GET /api/web/categories/:id
     */
    public function read($id)
    {
        $category = Category::find($id);
        if (!$category || $category->status !== 1) {
            return $this->error('栏目不存在', 404);
        }
        return $this->success($category);
    }

    private function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
