<?php
namespace app\api\middleware;

use app\common\model\CrawlerLog;

class CrawlerTracker
{
    public function handle($request, \Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        $userAgent = $request->header('User-Agent', '');

        // 只记录爬虫访问
        if (CrawlerLog::isCrawler($userAgent)) {
            $responseTime = intval(($endTime - $startTime) * 1000);
            $statusCode = $response->getCode();

            // 异步写入，不阻塞响应（用 ThinkPHP 的 think\Queue 或直接写）
            // 这里直接写，延迟很小
            try {
                CrawlerLog::log(
                    $userAgent,
                    $request->url(),
                    $request->method(),
                    $statusCode,
                    $request->ip(),
                    $request->header('Referer'),
                    $responseTime
                );
            } catch (\Exception $e) {
                // 记录失败不影响正常请求
            }
        }

        return $response;
    }
}
