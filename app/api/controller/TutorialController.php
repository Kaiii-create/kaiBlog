<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Tutorial;
use app\common\model\TutorialChapter;
use app\common\model\BrowseHistory;

class TutorialController extends BaseController
{
    use ApiResponse;

    /**
     * 教程列表（公开）
     * GET /api/web/tutorials
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $keyword = $this->request->get('keyword');

        $query = Tutorial::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'desc');

        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%");
        }

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 教程详情
     * GET /api/web/tutorials/:id
     */
    public function read($id)
    {
        $tutorial = Tutorial::with(['chapters' => function ($query) {
            $query->where('status', 1)->order('sort', 'asc');
        }])->find($id);

        if (!$tutorial || $tutorial->status !== 1) {
            return $this->error('教程不存在', 404);
        }

        Tutorial::where('id', $id)->inc('view_count')->update();
        $tutorial->view_count = $tutorial->view_count + 1;

        $userId = $this->request->user_id ?? null;
        BrowseHistory::create([
            'user_id' => $userId,
            'target_type' => 'tutorial',
            'target_id' => $id,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
        ]);

        return $this->success($tutorial);
    }

    /**
     * 教程章节列表
     * GET /api/web/tutorials/:id/chapters
     */
    public function chapters($id)
    {
        $tutorial = Tutorial::find($id);
        if (!$tutorial || $tutorial->status !== 1) {
            return $this->error('教程不存在', 404);
        }

        $chapters = TutorialChapter::where('tutorial_id', $id)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();

        $tree = $this->buildTree($chapters->toArray());
        return $this->success($tree);
    }

    /**
     * 章节详情
     * GET /api/web/chapters/:id
     */
    public function chapterRead($id)
    {
        $chapter = TutorialChapter::with('tutorial')->find($id);
        if (!$chapter || $chapter->status !== 1) {
            return $this->error('章节不存在', 404);
        }

        TutorialChapter::where('id', $id)->inc('view_count')->update();
        $chapter->view_count = $chapter->view_count + 1;

        $userId = $this->request->user_id ?? null;
        BrowseHistory::create([
            'user_id' => $userId,
            'target_type' => 'tutorial_chapter',
            'target_id' => $id,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
        ]);

        return $this->success($chapter);
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
