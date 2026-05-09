<?php
namespace app\common\model;

use think\Model;

class AiTask extends Model
{
    protected $table = 'ai_tasks';

    public function keyword()
    {
        return $this->belongsTo(AiKeyword::class, 'keyword_id');
    }

    public function channel()
    {
        return $this->belongsTo(AiChannel::class, 'channel_id');
    }

    public function template()
    {
        return $this->belongsTo(AiTemplate::class, 'template_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    /**
     * 获取待消费的任务
     */
    public static function getPending(int $limit = 5): \think\Collection
    {
        return self::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->whereOr('scheduled_at', '<=', date('Y-m-d H:i:s'));
            })
            ->order('id', 'asc')
            ->limit($limit)
            ->select();
    }

    /**
     * 获取待发布的任务(已完成且审核通过)
     */
    public static function getReadyToPublish(int $limit = 3): \think\Collection
    {
        return self::where('status', 'completed')
            ->order('completed_at', 'asc')
            ->limit($limit)
            ->select();
    }

    /**
     * 标记生成中
     */
    public function markGenerating(): void
    {
        $this->status = 'generating';
        $this->save();
    }

    /**
     * 标记完成
     */
    public function markCompleted(array $data): void
    {
        $this->status = 'completed';
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->markdown = $data['markdown'] ?? '';
        $this->summary = $data['summary'] ?? '';
        $this->tags = $data['tags'] ?? '';
        $this->seo_title = $data['seo_title'] ?? '';
        $this->seo_keywords = $data['seo_keywords'] ?? '';
        $this->seo_description = $data['seo_description'] ?? '';
        $this->input_tokens = $data['input_tokens'] ?? 0;
        $this->output_tokens = $data['output_tokens'] ?? 0;
        $this->cost_time = $data['cost_time'] ?? 0;
        $this->completed_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * 标记失败
     */
    public function markFailed(string $error): void
    {
        $this->retry_count += 1;
        if ($this->retry_count >= $this->max_retry) {
            $this->status = 'dead';
        } else {
            $this->status = 'failed';
        }
        $this->error_msg = $error;
        $this->save();
    }

    /**
     * 标记已发布
     */
    public function markPublished(int $articleId): void
    {
        $this->status = 'published';
        $this->article_id = $articleId;
        $this->published_at = date('Y-m-d H:i:s');
        $this->save();
    }
}
