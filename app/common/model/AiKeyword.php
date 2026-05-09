<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class AiKeyword extends Model
{
    use SoftDelete;
    protected $table = 'ai_keywords';
    protected $deleteTime = 'deleted_at';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function template()
    {
        return $this->belongsTo(AiTemplate::class, 'template_id');
    }

    public function channel()
    {
        return $this->belongsTo(AiChannel::class, 'channel_id');
    }

    /**
     * 获取待处理的关键词（还有剩余生成额度的）
     */
    public static function getPending(): \think\Collection
    {
        return self::where('status', 1)
            ->where('generated_count', '<', new \think\db\Raw('article_count'))
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 标记生成完成
     */
    public function markGenerated(): void
    {
        $this->generated_count += 1;
        if ($this->generated_count >= $this->article_count) {
            $this->status = 2; // 已完成
        }
        $this->save();
    }
}
