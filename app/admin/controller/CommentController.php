<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Comment;
use app\common\model\Message;

class CommentController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $status = $this->request->get('status');

        $query = Comment::with('user')->order('created_at', 'desc');
        if ($status !== null && $status !== '') $query->where('status', $status);

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 审核评论
     * PUT /api/admin/comments/:id/audit
     */
    public function audit($id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->error('评论不存在', 404);

        $data = $this->request->put();
        if (!in_array($data['status'], [0, 1, 2])) {
            return $this->error('状态值无效');
        }

        $oldStatus = $comment->status;
        $comment->status = $data['status'];
        $comment->save();

        // 审核通过或拒绝时通知用户
        if ($comment->user_id && $oldStatus != $data['status']) {
            if ($data['status'] == 1) {
                Message::send('user', $comment->user_id, 'approve',
                    '你的评论已通过审核',
                    mb_substr($comment->content, 0, 100),
                    [
                        'from_type' => 'admin',
                        'from_id' => $this->request->admin_id,
                        'target_type' => $comment->target_type,
                        'target_id' => $comment->target_id,
                    ]
                );
            } elseif ($data['status'] == 2) {
                Message::send('user', $comment->user_id, 'reject',
                    '你的评论未通过审核',
                    mb_substr($comment->content, 0, 100),
                    [
                        'from_type' => 'admin',
                        'from_id' => $this->request->admin_id,
                        'target_type' => $comment->target_type,
                        'target_id' => $comment->target_id,
                    ]
                );
            }
        }

        return $this->success($comment, '审核成功');
    }

    public function delete($id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->error('评论不存在', 404);
        Comment::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->success(null, '删除成功');
    }
}
