<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Category extends Model
{
    use SoftDelete;
    protected $table = 'categories';
    protected $deleteTime = 'deleted_at';

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
