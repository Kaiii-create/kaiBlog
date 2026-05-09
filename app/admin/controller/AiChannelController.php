<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\AiChannel;

class AiChannelController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 20);
        $query = AiChannel::order('sort', 'asc')->order('id', 'asc');
        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $channel = AiChannel::find($id);
        if (!$channel) return $this->error('渠道不存在', 404);
        return $this->success($channel);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['name' => 'require|max:100', 'type' => 'require', 'api_key' => 'require'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $channel = AiChannel::create($data);
        return $this->success($channel, '创建成功');
    }

    public function update($id)
    {
        $channel = AiChannel::find($id);
        if (!$channel) return $this->error('渠道不存在', 404);
        $channel->save($this->request->put());
        return $this->success($channel, '更新成功');
    }

    public function delete($id)
    {
        $channel = AiChannel::find($id);
        if (!$channel) return $this->error('渠道不存在', 404);
        $channel->delete();
        return $this->success(null, '删除成功');
    }

    /**
     * 测试渠道连通性
     * POST /admin/ai-channels/:id/test
     */
    public function test($id)
    {
        $channel = AiChannel::find($id);
        if (!$channel) return $this->error('渠道不存在', 404);

        try {
            $result = \app\common\service\AiService::generate(
                $channel,
                '请回复"连接成功"四个字',
                '你是一个测试助手，请简短回复。'
            );
            return $this->success([
                'response' => $result['content'] ?? '',
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost_time' => $result['cost_time'],
            ], '连接测试成功');
        } catch (\Exception $e) {
            return $this->error('连接测试失败: ' . $e->getMessage());
        }
    }
}
