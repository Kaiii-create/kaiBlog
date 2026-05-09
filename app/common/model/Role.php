<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Role extends Model
{
    use SoftDelete;
    protected $table = 'roles';
    protected $deleteTime = 'deleted_at';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'permission_id', 'role_id');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_roles', 'admin_id', 'role_id');
    }
}
