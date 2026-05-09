<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    use SoftDelete;
    protected $table = 'admins';
    protected $deleteTime = 'deleted_at';
    protected $hidden = ['password', 'token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles', 'role_id', 'admin_id');
    }
}
