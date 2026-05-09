<?php
namespace app\common\model;

use think\Model;

class AiSchedule extends Model
{
    protected $table = 'ai_schedules';

    /**
     * 获取所有启用的调度规则
     */
    public static function getActive(): \think\Collection
    {
        return self::where('status', 1)->order('id', 'asc')->select();
    }

    /**
     * 记录执行
     */
    public function recordRun(): void
    {
        $this->last_run_at = date('Y-m-d H:i:s');
        $this->run_count += 1;
        $this->save();
    }

    /**
     * 解析配置
     */
    public function getConfig(): array
    {
        return json_decode($this->config, true) ?? [];
    }
}
