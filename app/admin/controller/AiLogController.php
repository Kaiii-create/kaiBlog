<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\AiLog;

class AiLogController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $action = $this->request->get('action');
        $taskId = $this->request->get('task_id');

        $query = AiLog::order('id', 'desc');
        if ($action) $query->where('action', $action);
        if ($taskId) $query->where('task_id', $taskId);

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 统计汇总
     * GET /admin/ai-logs/stats
     */
    public function stats()
    {
        $today = date('Y-m-d');
        $totalTokens = AiLog::where('action', 'generate')->where('status', 'success')
            ->sum('input_tokens + output_tokens');
        $todayTokens = AiLog::where('action', 'generate')->where('status', 'success')
            ->whereDay('created_at')->sum('input_tokens + output_tokens');
        $totalGenerates = AiLog::where('action', 'generate')->where('status', 'success')->count();
        $todayGenerates = AiLog::where('action', 'generate')->where('status', 'success')
            ->whereDay('created_at')->count();
        $totalErrors = AiLog::where('status', 'failed')->count();
        $avgCostTime = AiLog::where('action', 'generate')->where('status', 'success')
            ->where('cost_time', '>', 0)->avg('cost_time');

        return $this->success([
            'total_tokens'     => intval($totalTokens),
            'today_tokens'     => intval($todayTokens),
            'total_generates'  => $totalGenerates,
            'today_generates'  => $todayGenerates,
            'total_errors'     => $totalErrors,
            'avg_cost_time'    => round($avgCostTime),
        ]);
    }
}
