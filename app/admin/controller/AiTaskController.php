<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\AiTask;
use app\common\model\AiLog;
use app\common\model\Article;
use app\common\model\ArticleTag;
use app\common\model\Tag;
use app\common\service\AiService;

class AiTaskController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $status = $this->request->get('status');
        $keywordId = $this->request->get('keyword_id');

        $query = AiTask::with(['keyword', 'channel', 'category'])->order('id', 'desc');
        if ($status) $query->where('status', $status);
        if ($keywordId) $query->where('keyword_id', $keywordId);

        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $task = AiTask::with(['keyword', 'channel', 'category', 'article'])->find($id);
        if (!$task) return $this->error('任务不存在', 404);
        return $this->success($task);
    }

    /**
     * 手动执行单个任务
     * POST /admin/ai-tasks/:id/run
     */
    public function run($id)
    {
        $task = AiTask::find($id);
        if (!$task) return $this->error('任务不存在', 404);
        if (!in_array($task->status, ['pending', 'failed'])) {
            return $this->error('任务状态不允许执行');
        }

        return $this->executeTask($task);
    }

    /**
     * 批量执行待处理任务
     * POST /admin/ai-tasks/consume
     */
    public function consume()
    {
        $batch = $this->request->post('batch', 5);
        $tasks = AiTask::getPending($batch);
        $results = [];

        foreach ($tasks as $task) {
            $results[] = $this->executeTask($task, true);
        }

        return $this->success($results, '批量执行完成');
    }

    /**
     * 发布已完成的任务为文章
     * POST /admin/ai-tasks/:id/publish
     */
    public function publish($id)
    {
        $task = AiTask::find($id);
        if (!$task) return $this->error('任务不存在', 404);
        if ($task->status !== 'completed') {
            return $this->error('只能发布已完成的任务');
        }

        return $this->publishTask($task);
    }

    /**
     * 批量发布
     * POST /admin/ai-tasks/batch-publish
     */
    public function batchPublish()
    {
        $batch = $this->request->post('batch', 3);
        $tasks = AiTask::getReadyToPublish($batch);
        $results = [];

        foreach ($tasks as $task) {
            $result = $this->publishTask($task, true);
            $results[] = $result;
        }

        return $this->success($results, '批量发布完成');
    }

    /**
     * 删除任务
     */
    public function delete($id)
    {
        $task = AiTask::find($id);
        if (!$task) return $this->error('任务不存在', 404);
        $task->delete();
        return $this->success(null, '删除成功');
    }

    /**
     * 清理死任务
     * DELETE /admin/ai-tasks/cleanup
     */
    public function cleanup()
    {
        $before = $this->request->get('before_date', date('Y-m-d', strtotime('-30 days')));
        $count = AiTask::where('status', 'dead')->where('created_at', '<', $before)->delete();
        return $this->success(['deleted' => $count], '清理完成');
    }

    /**
     * 统计概览
     * GET /admin/ai-tasks/stats
     */
    public function stats()
    {
        $stats = [
            'total'       => AiTask::count(),
            'pending'     => AiTask::where('status', 'pending')->count(),
            'generating'  => AiTask::where('status', 'generating')->count(),
            'completed'   => AiTask::where('status', 'completed')->count(),
            'failed'      => AiTask::where('status', 'failed')->count(),
            'dead'        => AiTask::where('status', 'dead')->count(),
            'published'   => AiTask::where('status', 'published')->count(),
            'today_generated' => AiTask::whereDay('completed_at')->count(),
            'today_published' => AiTask::whereDay('published_at')->count(),
        ];
        return $this->success($stats);
    }

    // ========== 私有方法 ==========

    private function executeTask(AiTask $task, bool $silent = false): array
    {
        $task->markGenerating();

        try {
            $channel = $task->channel_id
                ? \app\common\model\AiChannel::getAvailableById($task->channel_id)
                : \app\common\model\AiChannel::getAvailable();

            if (!$channel) {
                throw new \Exception('没有可用的AI渠道');
            }

            $template = $task->template_id
                ? \app\common\model\AiTemplate::find($task->template_id)
                : \app\common\model\AiTemplate::where('status', 1)->order('id', 'asc')->find();

            $category = $task->category_id
                ? \app\common\model\Category::find($task->category_id)
                : null;

            $keyword = $task->keyword
                ? $task->keyword->keyword
                : '技术文章';

            $vars = [
                'keyword'  => $keyword,
                'category' => $category ? $category->name : '未分类',
                'style'    => '技术博客',
            ];

            $prompt = $template ? $template->render($vars) : "请围绕「{$keyword}」写一篇技术文章，返回JSON格式。";
            $systemPrompt = $template ? $template->system_prompt : null;

            // 更新任务的 prompt
            $task->prompt = $prompt;
            $task->channel_id = $channel->id;
            $task->save();

            $result = AiService::generate($channel, $prompt, $systemPrompt);
            $task->markCompleted($result);

            // 记录日志
            AiLog::log($task->id, $channel->id, 'generate', [
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost_time'     => $result['cost_time'],
                'status'        => 'success',
            ]);

            // 更新关键词生成计数
            if ($task->keyword_id) {
                $task->keyword->markGenerated();
            }

            $data = ['task_id' => $task->id, 'status' => 'completed', 'title' => $result['title']];
            return $silent ? $data : $this->success($data, '生成成功');

        } catch (\Exception $e) {
            $task->markFailed($e->getMessage());

            AiLog::log($task->id, $task->channel_id, 'error', [
                'status'    => 'failed',
                'error_msg' => $e->getMessage(),
            ]);

            $data = ['task_id' => $task->id, 'status' => 'failed', 'error' => $e->getMessage()];
            return $silent ? $data : $this->error('生成失败: ' . $e->getMessage());
        }
    }

    private function publishTask(AiTask $task, bool $silent = false): array
    {
        try {
            $tags = array_filter(array_map('trim', explode(',', $task->tags ?? '')));

            $article = Article::create([
                'category_id'     => $task->category_id,
                'admin_id'        => 1, // 系统生成
                'title'           => $task->title,
                'content'         => $task->content,
                'markdown'        => $task->markdown,
                'summary'         => $task->summary,
                'status'          => 1, // 直接发布
                'seo_title'       => $task->seo_title,
                'seo_keywords'    => $task->seo_keywords,
                'seo_description' => $task->seo_description,
                'published_at'    => date('Y-m-d H:i:s'),
            ]);

            // 处理标签
            foreach ($tags as $tagName) {
                $tag = Tag::where('name', $tagName)->find();
                if (!$tag) {
                    $tag = Tag::create(['name' => $tagName, 'slug' => $tagName]);
                }
                ArticleTag::create(['article_id' => $article->id, 'tag_id' => $tag->id]);
                $tag->article_count = ArticleTag::where('tag_id', $tag->id)->count();
                $tag->save();
            }

            $task->markPublished($article->id);

            // 更新关键词发布计数
            if ($task->keyword_id) {
                $keyword = $task->keyword;
                $keyword->published_count += 1;
                $keyword->save();
            }

            AiLog::log($task->id, $task->channel_id, 'publish', [
                'status' => 'success',
                'extra'  => ['article_id' => $article->id],
            ]);

            $data = ['task_id' => $task->id, 'article_id' => $article->id, 'title' => $article->title];
            return $silent ? $data : $this->success($data, '发布成功');

        } catch (\Exception $e) {
            AiLog::log($task->id, $task->channel_id, 'publish', [
                'status'    => 'failed',
                'error_msg' => $e->getMessage(),
            ]);

            $data = ['task_id' => $task->id, 'error' => $e->getMessage()];
            return $silent ? $data : $this->error('发布失败: ' . $e->getMessage());
        }
    }
}
