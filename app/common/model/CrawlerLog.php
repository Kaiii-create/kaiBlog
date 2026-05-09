<?php
namespace app\common\model;

use think\Model;

class CrawlerLog extends Model
{
    protected $table = 'crawler_logs';
    protected $autoWriteTimestamp = 'datetime(3)';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    // 爬虫类型映射
    protected static $botPatterns = [
        'google'     => ['Googlebot', 'Google-InspectionTool', 'Google-Site-Verification'],
        'baidu'      => ['Baiduspider', 'Baiduspider-render', 'Baiduspider-image', 'Baiduspider-video', 'Baiduspider-news'],
        'bing'       => ['Bingbot', 'BingPreview', 'msnbot', 'AdIdxBot'],
        'yandex'     => ['YandexBot', 'YandexMobileBot', 'YandexMetrika'],
        'sogou'      => ['Sogou', 'SogouPicSpider', 'SogouNewsSpider', 'SogouPushSpider'],
        'bytespider' => ['Bytespider'],
        'duckduckgo' => ['DuckDuckBot'],
        'apple'      => ['Applebot'],
        'yahoo'      => ['Slurp'],
        'semrush'    => ['SemrushBot', 'SemrushBot-SA'],
        'ahrefs'     => ['AhrefsBot'],
        'mj12bot'    => ['MJ12bot'],
        'dotbot'     => ['DotBot'],
        'petalbot'   => ['PetalBot'],
    ];

    /**
     * 识别爬虫类型
     */
    public static function detectBot(string $userAgent): array
    {
        $ua = strtolower($userAgent);
        foreach (self::$botPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($userAgent, $pattern) !== false) {
                    return [
                        'type' => $type,
                        'name' => $pattern,
                        'is_mobile' => stripos($ua, 'mobile') !== false ? 1 : 0,
                    ];
                }
            }
        }
        return ['type' => 'unknown', 'name' => null, 'is_mobile' => 0];
    }

    /**
     * 判断是否为爬虫
     */
    public static function isCrawler(string $userAgent): bool
    {
        if (empty($userAgent)) return false;
        $ua = strtolower($userAgent);
        $keywords = ['bot', 'spider', 'crawl', 'slurp', 'mediapartners', 'feedfetcher', 'archive'];
        foreach ($keywords as $kw) {
            if (strpos($ua, $kw) !== false) return true;
        }
        return false;
    }

    /**
     * 记录爬虫访问
     */
    public static function log(string $userAgent, string $requestUrl, string $method = 'GET', ?int $statusCode = null, ?string $ip = null, ?string $referer = null, ?int $responseTime = null): ?self
    {
        if (!self::isCrawler($userAgent)) return null;

        $bot = self::detectBot($userAgent);

        return self::create([
            'bot_type'       => $bot['type'],
            'bot_name'       => $bot['name'],
            'user_agent'     => mb_substr($userAgent, 0, 500),
            'ip_address'     => $ip,
            'request_url'    => mb_substr($requestUrl, 0, 2000),
            'request_method' => $method,
            'status_code'    => $statusCode,
            'response_time'  => $responseTime,
            'referer'        => $referer ? mb_substr($referer, 0, 2000) : null,
            'is_mobile'      => $bot['is_mobile'],
        ]);
    }

    /**
     * 获取爬虫统计（按类型分组）
     */
    public static function getStats(string $startDate, string $endDate): array
    {
        return self::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->field('bot_type, COUNT(*) as crawl_count, COUNT(DISTINCT request_url) as unique_pages')
            ->group('bot_type')
            ->order('crawl_count', 'desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取每日趋势
     */
    public static function getDailyTrend(string $startDate, string $endDate): array
    {
        return self::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->field("DATE(created_at) as stat_date, bot_type, COUNT(*) as crawl_count, COUNT(DISTINCT request_url) as unique_pages")
            ->group('stat_date, bot_type')
            ->order('stat_date', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取最近被爬取的页面 Top N
     */
    public static function getTopPages(int $limit = 20, string $startDate = null, string $endDate = null): array
    {
        $query = self::field('request_url, COUNT(*) as crawl_count, MAX(created_at) as last_crawl')
            ->group('request_url')
            ->order('crawl_count', 'desc')
            ->limit($limit);

        if ($startDate) $query->where('created_at', '>=', $startDate);
        if ($endDate) $query->where('created_at', '<=', $endDate . ' 23:59:59');

        return $query->select()->toArray();
    }
}
