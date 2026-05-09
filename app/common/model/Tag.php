<?php
namespace app\common\model;

use think\Model;

class Tag extends Model
{
    protected $table = 'tags';

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tags', 'article_id', 'tag_id');
    }
}
