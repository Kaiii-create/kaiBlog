<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\{Article, Category, Tutorial, User, Comment, Admin};

class Dashboard extends BaseController
{
    use ApiResponse;

    /**
     * 控制台统计
     * GET /api/admin/dashboard
     */
    public function index()
    {
        return $this->success([
            'article_count' => Article::count(),
            'category_count' => Category::count(),
            'tutorial_count' => Tutorial::count(),
            'user_count' => User::count(),
            'comment_count' => Comment::count(),
            'admin_count' => Admin::count(),
            'recent_articles' => Article::order('created_at', 'desc')->limit(5)->select(),
            'recent_comments' => Comment::with('user')->order('created_at', 'desc')->limit(5)->select(),
        ]);
    }
}
