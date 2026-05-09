<?php
namespace app\common\model;

use think\Model;

class BrowseHistory extends Model
{
    protected $table = 'browse_histories';
    public $timestamps = false;
    protected $autoWriteTimestamp = false;
}
