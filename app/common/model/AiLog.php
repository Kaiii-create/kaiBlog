<?php
namespace app\common\model;

use think\Model;

class AiLog extends Model
{
    protected $table = 'ai_logs';
    protected $updateTime = false;

    /**
     * 记录日志
     */
    public static function log(int $taskId, ?int $channelId, string $action, array $data = []): self
    {
        return self::create([
            'task_id'      => $taskId,
            'channel_id'   => $channelId,
            'action'       => $action,
            'input_tokens' => $data['input_tokens'] ?? null,
            'output_tokens'=> $data['output_tokens'] ?? null,
            'cost_time'    => $data['cost_time'] ?? null,
            'status'       => $data['status'] ?? 'success',
            'error_msg'    => $data['error_msg'] ?? null,
            'extra'        => !empty($data['extra']) ? json_encode($data['extra']) : null,
        ]);
    }
}
