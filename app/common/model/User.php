<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class User extends Model
{
    use SoftDelete;
    protected $table = 'users';
    protected $deleteTime = 'deleted_at';
    protected $hidden = ['password'];
}
