<?php
namespace app\common\model;

use think\Model;

class AiChannel extends Model
{
    protected $table = 'ai_channels';

    // 默认 API 地址映射
    const DEFAULT_URLS = [
        'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
        'qianwen'  => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
        'wenxin'   => 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat',
        'openai'   => 'https://api.openai.com/v1/chat/completions',
        'claude'   => 'https://api.anthropic.com/v1/messages',
    ];

    const DEFAULT_MODELS = [
        'deepseek' => 'deepseek-chat',
        'qianwen'  => 'qwen-plus',
        'wenxin'   => 'ernie-4.0-8k',
        'openai'   => 'gpt-4o-mini',
        'claude'   => 'claude-3-5-sonnet-20241022',
    ];

    /**
     * 获取可用渠道（按优先级）
     */
    public static function getAvailable(): ?self
    {
        $today = date('Y-m-d');
        $channel = self::where('status', 1)
            ->where(function ($q) use ($today) {
                $q->where('daily_reset_at', '<>', $today)
                    ->whereOr('daily_used', '<', new \think\db\Raw('daily_quota'));
            })
            ->order('sort', 'asc')
            ->find();

        // 如果今日额度已重置但用完了，跳过
        if ($channel && $channel->daily_reset_at === $today && $channel->daily_used >= $channel->daily_quota) {
            return null;
        }

        return $channel;
    }

    /**
     * 指定渠道ID获取（检查可用性）
     */
    public static function getAvailableById(int $id): ?self
    {
        $channel = self::find($id);
        if (!$channel || $channel->status !== 1) return null;

        $today = date('Y-m-d');
        if ($channel->daily_reset_at === $today && $channel->daily_used >= $channel->daily_quota) {
            return null;
        }
        return $channel;
    }

    /**
     * 消耗一次额度
     */
    public function consume(): void
    {
        $today = date('Y-m-d');
        if ($this->daily_reset_at !== $today) {
            $this->daily_reset_at = $today;
            $this->daily_used = 0;
        }
        $this->daily_used += 1;
        $this->save();
    }

    /**
     * 获取API地址
     */
    public function getApiUrl(): string
    {
        if ($this->api_url) return $this->api_url;
        return self::DEFAULT_URLS[$this->type] ?? self::DEFAULT_URLS['openai'];
    }

    /**
     * 获取模型名称
     */
    public function getModelName(): string
    {
        if ($this->model_name) return $this->model_name;
        return self::DEFAULT_MODELS[$this->type] ?? 'gpt-4o-mini';
    }
}
