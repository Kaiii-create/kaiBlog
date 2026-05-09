<?php
namespace app\common\model;

use think\Model;

class Favorite extends Model
{
    protected $table = 'favorites';
    public $timestamps = false;
}
