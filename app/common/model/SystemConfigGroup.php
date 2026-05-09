<?php
namespace app\common\model;

use think\Model;

class SystemConfigGroup extends Model
{
    protected $table = 'system_config_groups';

    public function configs()
    {
        return $this->hasMany(SystemConfig::class, 'config_group', 'name')
            ->order('sort', 'asc')
            ->order('id', 'asc');
    }

    /**
     * 获取所有分组（含配置项数量）
     */
    public static function getAllWithCount(): \think\Collection
    {
        return self::withCount('configs')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();
    }
}
