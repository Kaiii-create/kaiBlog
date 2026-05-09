<?php
namespace app\common\model;

use think\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id')->order('sort', 'asc');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * 获取树形权限列表
     */
    public static function getTree($parentId = 0): array
    {
        $items = self::order('sort', 'asc')->order('id', 'asc')->select()->toArray();
        return self::buildTree($items, $parentId);
    }

    private static function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = self::buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
