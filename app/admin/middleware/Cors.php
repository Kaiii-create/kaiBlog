<?php
namespace app\admin\middleware;

class Cors
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof \think\Response) {
            $response->header([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        if ($request->method(true) === 'OPTIONS') {
            return response('', 204);
        }

        return $response;
    }
}
