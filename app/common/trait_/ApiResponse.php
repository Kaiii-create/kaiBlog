<?php
namespace app\common\trait_;

trait ApiResponse
{
    protected function success($data = null, string $message = 'success', int $code = 0): \think\response\Json
    {
        return json(['code' => $code, 'message' => $message, 'data' => $data]);
    }

    protected function error(string $message = 'error', int $code = 400, $data = null): \think\response\Json
    {
        return json(['code' => $code, 'message' => $message, 'data' => $data]);
    }

    protected function unauthorized(string $message = '请先登录'): \think\response\Json
    {
        return json(['code' => 401, 'message' => $message, 'data' => null]);
    }

    protected function forbidden(string $message = '无权限'): \think\response\Json
    {
        return json(['code' => 403, 'message' => $message, 'data' => null]);
    }

    protected function paginate($query, int $page = 1, int $pageSize = 10): \think\response\Json
    {
        $total = $query->count();
        $list = $query->page($page, $pageSize)->select();
        return $this->success([
            'list' => $list,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
                'total_page' => ceil($total / $pageSize),
            ]
        ]);
    }
}
