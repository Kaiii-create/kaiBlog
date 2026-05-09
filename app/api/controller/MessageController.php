<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Message;

class MessageController extends BaseController
{
    use ApiResponse;

    /**
     * 用户消息列表
     * GET /api/user/messages?is_read=0&page=1&page_size=10&type=comment
     */
    public function index()
    {
        $userId = $this->request->user_id;
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $isRead = $this->request->get('is_read');
        $type = $this->request->get('type');

        $query = Message::where('receiver_type', 'user')
            ->where('receiver_id', $userId)
            ->order('created_at', 'desc');

        if ($isRead !== null && $isRead !== '') {
            $query->where('is_read', $isRead);
        }
        if ($type) {
            $query->where('type', $type);
        }

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 未读数量
     * GET /api/user/messages/unread-count
     */
    public function unreadCount()
    {
        $count = Message::unreadCount('user', $this->request->user_id);
        return $this->success(['count' => $count]);
    }

    /**
     * 标记单条已读
     * PUT /api/user/messages/:id/read
     */
    public function markRead($id)
    {
        $ok = Message::markRead($id, 'user', $this->request->user_id);
        if (!$ok) return $this->error('消息不存在');
        return $this->success(null, '已读');
    }

    /**
     * 全部标记已读
     * PUT /api/user/messages/read-all
     */
    public function markAllRead()
    {
        $count = Message::markAllRead('user', $this->request->user_id);
        return $this->success(['count' => $count], "已标记 {$count} 条为已读");
    }

    /**
     * 删除消息
     * DELETE /api/user/messages/:id
     */
    public function delete($id)
    {
        $msg = Message::where('id', $id)
            ->where('receiver_type', 'user')
            ->where('receiver_id', $this->request->user_id)
            ->find();
        if (!$msg) return $this->error('消息不存在', 404);
        $msg->delete();
        return $this->success(null, '删除成功');
    }
}
