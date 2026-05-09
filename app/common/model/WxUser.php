<?php
namespace app\common\model;

use think\Model;

class WxUser extends Model
{
    protected $table = 'wx_users';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
