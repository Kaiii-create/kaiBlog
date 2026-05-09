<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Article extends Model
{
    use SoftDelete;
    protected $table = 'articles';
    protected $deleteTime = 'deleted_at';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tags', 'tag_id', 'article_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'target_id')->where('target_type', 'article');
    }
}
