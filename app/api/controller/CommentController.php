<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Comment;
use app\common\model\Message;
use app\common\model\User;
use app\common\model\Admin;

class CommentController extends BaseController
{
    use ApiResponse;

    /**
     * 评论列表（公开）
     * GET /api/comments
     */
    public function index()
    {
        $targetType = $this->request->get('target_type', 'article');
        $targetId = $this->request->get('target_id');
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);

        if (!$targetId) {
            return $this->error('缺少target_id参数');
        }

        $query = Comment::with(['user', 'children.user'])
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->order('created_at', 'desc');

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 发表评论（需要登录）
     * POST /api/user/comments
     */
    public function save()
    {
        $data = $this->request->post();
        if (empty($data['target_type']) || empty($data['target_id']) || empty($data['content'])) {
            return $this->error('参数不完整');
        }

        if (strlen($data['content']) < 2 || strlen($data['content']) > 1000) {
            return $this->error('评论内容长度2-1000个字符');
        }

        $userId = $this->request->user_id;
        $user = User::find($userId);

        $comment = Comment::create([
            'user_id' => $userId,
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'],
            'parent_id' => $data['parent_id'] ?? 0,
            'content' => $data['content'],
            'status' => 1,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
        ]);

        // 更新评论数
        if ($data['target_type'] === 'article') {
            \app\common\model\Article::where('id', $data['target_id'])->inc('comment_count');
        }

        // === 发送通知 ===

        // 1. 通知管理员：有新评论
        $admins = Admin::where('status', 1)->select();
        $userName = $user->nickname ?? $user->username;
        $targetName = $this->getTargetName($data['target_type'], $data['target_id']);
        foreach ($admins as $admin) {
            Message::send('admin', $admin->id, 'comment',
                "{$userName} 评论了{$targetName}",
                mb_substr($data['content'], 0, 100),
                [
                    'from_type' => 'user',
                    'from_id' => $userId,
                    'target_type' => $data['target_type'],
                    'target_id' => $data['target_id'],
                ]
            );
        }

        // 2. 如果是回复评论，通知被回复的用户
        if (!empty($data['parent_id'])) {
            $parentComment = Comment::find($data['parent_id']);
            if ($parentComment && $parentComment->user_id && $parentComment->user_id != $userId) {
                Message::send('user', $parentComment->user_id, 'reply',
                    "{$userName} 回复了你的评论",
                    mb_substr($data['content'], 0, 100),
                    [
                        'from_type' => 'user',
                        'from_id' => $userId,
                        'target_type' => $data['target_type'],
                        'target_id' => $data['target_id'],
                    ]
                );
            }
        }

        return $this->success($comment, '评论成功');
    }

    private function getTargetName(string $targetType, int $targetId): string
    {
        switch ($targetType) {
            case 'article':
                $article = \app\common\model\Article::find($targetId);
                return $article ? '文章《' . $article->title . '》' : '文章';
            case 'tutorial_chapter':
                $chapter = \app\common\model\TutorialChapter::find($targetId);
                return $chapter ? '章节《' . $chapter->title . '》' : '章节';
            default:
                return '内容';
        }
    }
}
