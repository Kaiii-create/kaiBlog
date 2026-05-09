<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\TutorialChapter;
use app\common\model\Tutorial;

class ChapterController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $tutorialId = $this->request->get('tutorial_id');
        if (!$tutorialId) return $this->error('缺少tutorial_id');

        $chapters = TutorialChapter::where('tutorial_id', $tutorialId)
            ->order('sort', 'asc')
            ->select();

        $tree = $this->buildTree($chapters->toArray());
        return $this->success($tree);
    }

    public function read($id)
    {
        $chapter = TutorialChapter::find($id);
        if (!$chapter) return $this->error('章节不存在', 404);
        return $this->success($chapter);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['tutorial_id' => 'require', 'title' => 'require|max:200'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $chapter = TutorialChapter::create($data);

        // 更新教程章节数
        $count = TutorialChapter::where('tutorial_id', $data['tutorial_id'])->count();
        Tutorial::where('id', $data['tutorial_id'])->update(['chapter_count' => $count]);

        return $this->success($chapter, '创建成功');
    }

    public function update($id)
    {
        $chapter = TutorialChapter::find($id);
        if (!$chapter) return $this->error('章节不存在', 404);
        $chapter->save($this->request->put());
        return $this->success($chapter, '更新成功');
    }

    public function delete($id)
    {
        $chapter = TutorialChapter::find($id);
        if (!$chapter) return $this->error('章节不存在', 404);
        $tutorialId = $chapter->tutorial_id;
        TutorialChapter::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $count = TutorialChapter::where('tutorial_id', $tutorialId)->count();
        Tutorial::where('id', $tutorialId)->update(['chapter_count' => $count]);

        return $this->success(null, '删除成功');
    }

    /**
     * 批量排序
     * PUT /api/admin/chapters/sort
     */
    public function sort()
    {
        $data = $this->request->put();
        if (empty($data['sorts'])) return $this->error('参数错误');

        foreach ($data['sorts'] as $item) {
            TutorialChapter::where('id', $item['id'])->update(['sort' => $item['sort']]);
        }
        return $this->success(null, '排序成功');
    }

    private function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) $item['children'] = $children;
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
