<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\CrawlerLog;

class CrawlerController extends BaseController
{
    use ApiResponse;

    /**
     * 爬虫访问日志列表
     * GET /api/admin/crawlers?page=1&page_size=20&bot_type=google&start_date=&end_date=
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $botType = $this->request->get('bot_type');
        $startDate = $this->request->get('start_date');
        $endDate = $this->request->get('end_date');
        $url = $this->request->get('url');

        $query = CrawlerLog::order('created_at', 'desc');

        if ($botType) $query->where('bot_type', $botType);
        if ($startDate) $query->where('created_at', '>=', $startDate);
        if ($endDate) $query->where('created_at', '<=', $endDate . ' 23:59:59');
        if ($url) $query->where('request_url', 'like', "%{$url}%");

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 爬虫统计概览
     * GET /api/admin/crawlers/stats?start_date=&end_date=
     */
    public function stats()
    {
        $startDate = $this->request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $this->request->get('end_date', date('Y-m-d'));

        // 总数
        $totalCrawls = CrawlerLog::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->count();
        $totalPages = CrawlerLog::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->count('DISTINCT request_url');

        // 今日
        $todayCrawls = CrawlerLog::whereDay('created_at')->count();
        $todayPages = CrawlerLog::whereDay('created_at')->count('DISTINCT request_url');

        // 按类型统计
        $byType = CrawlerLog::getStats($startDate, $endDate);

        // 每日趋势
        $dailyTrend = CrawlerLog::getDailyTrend($startDate, $endDate);

        // Top 页面
        $topPages = CrawlerLog::getTopPages(20, $startDate, $endDate);

        // 最近访问
        $recentLogs = CrawlerLog::order('created_at', 'desc')->limit(20)->select();

        return $this->success([
            'summary' => [
                'total_crawls' => $totalCrawls,
                'total_pages' => $totalPages,
                'today_crawls' => $todayCrawls,
                'today_pages' => $todayPages,
            ],
            'by_type' => $byType,
            'daily_trend' => $dailyTrend,
            'top_pages' => $topPages,
            'recent_logs' => $recentLogs,
        ]);
    }

    /**
     * 爬虫类型列表（用于筛选下拉）
     * GET /api/admin/crawlers/bot-types
     */
    public function botTypes()
    {
        $types = CrawlerLog::field('bot_type, COUNT(*) as count')
            ->group('bot_type')
            ->order('count', 'desc')
            ->select();

        return $this->success($types);
    }

    /**
     * 清理过期日志
     * DELETE /api/admin/crawlers/cleanup?before_date=2026-01-01
     */
    public function cleanup()
    {
        $beforeDate = $this->request->get('before_date', date('Y-m-d', strtotime('-90 days')));

        $count = CrawlerLog::where('created_at', '<', $beforeDate)->delete();

        return $this->success(['deleted' => $count], '清理完成');
    }
}
