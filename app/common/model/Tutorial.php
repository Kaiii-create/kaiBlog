<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Tutorial extends Model
{
    use SoftDelete;
    protected $table = 'tutorials';
    protected $deleteTime = 'deleted_at';

    public function chapters()
    {
        return $this->hasMany(TutorialChapter::class, 'tutorial_id')->order('sort', 'asc');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
