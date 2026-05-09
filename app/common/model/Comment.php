<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comment extends Model
{
    use SoftDelete;
    protected $table = 'comments';
    protected $deleteTime = 'deleted_at';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
