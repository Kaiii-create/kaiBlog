<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Article;
use app\common\model\BrowseHistory;
use app\common\model\Favorite;
use app\common\model\Like;

class ArticleController extends BaseController
{
    use ApiResponse;

    /**
     * 文章列表（公开）
     * GET /api/web/articles
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $categoryId = $this->request->get('category_id');
        $tagId = $this->request->get('tag_id');
        $keyword = $this->request->get('keyword');

        $query = Article::with(['category', 'admin', 'tags'])
            ->where('status', 1)
            ->order('is_top', 'desc')
            ->order('published_at', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%");
        }
        if ($tagId) {
            $articleIds = \app\common\model\ArticleTag::where('tag_id', $tagId)->column('article_id');
            $query->whereIn('id', $articleIds);
        }

        return $this->paginate($query, $page, $pageSize);
    }

    /**
     * 文章详情（公开）
     * GET /api/web/articles/:id
     */
    public function read($id)
    {
        $article = Article::with(['category', 'admin', 'tags'])->find($id);
        if (!$article || $article->status !== 1) {
            return $this->error('文章不存在', 404);
        }

        // 增加浏览量
        Article::where('id', $id)->inc('view_count')->update();
        $article->view_count = $article->view_count + 1;

        // 记录浏览历史
        $userId = $this->request->user_id ?? null;
        BrowseHistory::create([
            'user_id' => $userId,
            'target_type' => 'article',
            'target_id' => $id,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
        ]);

        return $this->success($article);
    }

    /**
     * 文章点赞
     * POST /api/user/articles/:id/like
     */
    public function like($id)
    {
        $userId = $this->request->user_id;
        $article = Article::find($id);
        if (!$article) return $this->error('文章不存在', 404);

        $exist = Like::where('user_id', $userId)
            ->where('target_type', 'article')
            ->where('target_id', $id)
            ->find();

        if ($exist) {
            $exist->delete();
            $article->dec('like_count');
            return $this->success(['liked' => false], '取消点赞');
        } else {
            Like::create([
                'user_id' => $userId,
                'target_type' => 'article',
                'target_id' => $id,
            ]);
            $article->inc('like_count');
            return $this->success(['liked' => true], '点赞成功');
        }
    }

    /**
     * 文章收藏
     * POST /api/user/articles/:id/favorite
     */
    public function favorite($id)
    {
        $userId = $this->request->user_id;
        $article = Article::find($id);
        if (!$article) return $this->error('文章不存在', 404);

        $exist = Favorite::where('user_id', $userId)
            ->where('target_type', 'article')
            ->where('target_id', $id)
            ->find();

        if ($exist) {
            $exist->delete();
            return $this->success(['favorited' => false], '取消收藏');
        } else {
            Favorite::create([
                'user_id' => $userId,
                'target_type' => 'article',
                'target_id' => $id,
            ]);
            return $this->success(['favorited' => true], '收藏成功');
        }
    }

    /**
     * 用户收藏列表
     * GET /api/user/favorites
     */
    public function favorites()
    {
        $userId = $this->request->user_id;
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);

        $query = Favorite::where('user_id', $userId)
            ->where('target_type', 'article')
            ->order('created_at', 'desc');

        $total = $query->count();
        $favorites = $query->page($page, $pageSize)->select();
        $articleIds = $favorites->column('target_id');
        $articles = Article::whereIn('id', $articleIds)->with(['category'])->select();

        return $this->success([
            'list' => $articles,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
                'total_page' => ceil($total / $pageSize),
            ]
        ]);
    }
}
