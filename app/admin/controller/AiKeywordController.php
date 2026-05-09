<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\AiKeyword;
use app\common\model\AiTask;
use app\common\model\AiTemplate;

class AiKeywordController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $status = $this->request->get('status');
        $keyword = $this->request->get('keyword');

        $query = AiKeyword::with(['category', 'template', 'channel'])->order('id', 'desc');
        if ($status !== null && $status !== '') $query->where('status', $status);
        if ($keyword) $query->where('keyword', 'like', "%{$keyword}%");

        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $item = AiKeyword::with(['category', 'template', 'channel'])->find($id);
        if (!$item) return $this->error('不存在', 404);
        return $this->success($item);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['keyword' => 'require|max:200'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        // 如果没指定模板，使用默认模板
        if (empty($data['template_id'])) {
            $default = AiTemplate::where('status', 1)->order('id', 'asc')->find();
            if ($default) $data['template_id'] = $default->id;
        }

        $item = AiKeyword::create($data);
        return $this->success($item, '创建成功');
    }

    public function update($id)
    {
        $item = AiKeyword::find($id);
        if (!$item) return $this->error('不存在', 404);
        $item->save($this->request->put());
        return $this->success($item, '更新成功');
    }

    public function delete($id)
    {
        $item = AiKeyword::find($id);
        if (!$item) return $this->error('不存在', 404);
        AiKeyword::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->success(null, '删除成功');
    }

    /**
     * 手动触发生成任务
     * POST /admin/ai-keywords/:id/generate
     */
    public function generate($id)
    {
        $keyword = AiKeyword::find($id);
        if (!$keyword) return $this->error('不存在', 404);

        $count = $this->request->post('count', 1);
        $tasks = [];

        for ($i = 0; $i < $count; $i++) {
            if ($keyword->generated_count + count($tasks) >= $keyword->article_count) break;

            $task = AiTask::create([
                'keyword_id'  => $keyword->id,
                'channel_id'  => $keyword->channel_id,
                'template_id' => $keyword->template_id,
                'category_id' => $keyword->category_id,
                'status'      => 'pending',
            ]);
            $tasks[] = $task->id;
        }

        return $this->success(['task_ids' => $tasks, 'count' => count($tasks)], '任务已创建');
    }

    /**
     * 批量生成任务（所有启用的关键词）
     * POST /admin/ai-keywords/batch-generate
     */
    public function batchGenerate()
    {
        $count = $this->request->post('count', 10);
        $keywords = AiKeyword::getPending();
        $created = 0;

        foreach ($keywords as $keyword) {
            for ($i = 0; $i < $count; $i++) {
                if ($keyword->generated_count >= $keyword->article_count) break;

                AiTask::create([
                    'keyword_id'  => $keyword->id,
                    'channel_id'  => $keyword->channel_id,
                    'template_id' => $keyword->template_id,
                    'category_id' => $keyword->category_id,
                    'status'      => 'pending',
                ]);
                $created++;
            }
        }

        return $this->success(['count' => $created], "已创建 {$created} 个任务");
    }
}
