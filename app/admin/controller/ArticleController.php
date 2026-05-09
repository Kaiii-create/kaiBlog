<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Article;
use app\common\model\ArticleTag;
use app\common\model\Tag;

class ArticleController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('page_size', 10);
        $status = $this->request->get('status');
        $categoryId = $this->request->get('category_id');
        $keyword = $this->request->get('keyword');

        $query = Article::with(['category', 'admin', 'tags']);
        if ($status !== null && $status !== '') $query->where('status', $status);
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($keyword) $query->where('title', 'like', "%{$keyword}%");
        $query->order('id', 'desc');

        return $this->paginate($query, $page, $pageSize);
    }

    public function read($id)
    {
        $article = Article::with(['category', 'tags'])->find($id);
        if (!$article) return $this->error('文章不存在', 404);
        return $this->success($article);
    }

    public function save()
    {
        $data = $this->request->post();
        $rules = ['title' => 'require|max:200', 'content' => 'require'];
        $validate = new \think\Validate($rules);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $data['admin_id'] = $this->request->admin_id;
        if ($data['status'] == 1 && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $article = Article::create($data);

        // 处理标签
        if (!empty($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        return $this->success($article, '创建成功');
    }

    public function update($id)
    {
        $article = Article::find($id);
        if (!$article) return $this->error('文章不存在', 404);

        $data = $this->request->put();
        if (isset($data['status']) && $data['status'] == 1 && empty($article->published_at)) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        $article->save($data);

        if ($tags !== null) {
            $this->syncTags($article, $tags);
        }

        return $this->success($article, '更新成功');
    }

    public function delete($id)
    {
        $article = Article::find($id);
        if (!$article) return $this->error('文章不存在', 404);
        Article::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        ArticleTag::where('article_id', $id)->delete();
        return $this->success(null, '删除成功');
    }

    private function syncTags($article, array $tagNames)
    {
        ArticleTag::where('article_id', $article->id)->delete();
        foreach ($tagNames as $tagName) {
            $tag = Tag::where('name', $tagName)->find();
            if (!$tag) {
                $tag = Tag::create(['name' => $tagName, 'slug' => $tagName]);
            }
            ArticleTag::create(['article_id' => $article->id, 'tag_id' => $tag->id]);
            $tag->article_count = ArticleTag::where('tag_id', $tag->id)->count();
            $tag->save();
        }
    }
}
