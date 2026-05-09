<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class TutorialChapter extends Model
{
    use SoftDelete;
    protected $table = 'tutorial_chapters';
    protected $deleteTime = 'deleted_at';

    public function tutorial()
    {
        return $this->belongsTo(Tutorial::class, 'tutorial_id');
    }

    public function children()
    {
        return $this->hasMany(TutorialChapter::class, 'parent_id')->order('sort', 'asc');
    }
}
