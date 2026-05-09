<?php
namespace app\common\model;

use think\Model;

class SystemConfig extends Model
{
    protected $table = 'system_configs';

    public static function getConfig(string $key, $default = null)
    {
        $config = self::where('config_key', $key)->find();
        if (!$config) return $default;
        return self::parseValue($config);
    }

    public static function getConfigs(array $keys = []): array
    {
        $query = self::order('sort', 'asc')->order('id', 'asc');
        if (!empty($keys)) {
            $query->whereIn('config_key', $keys);
        }
        $configs = $query->select();
        $result = [];
        foreach ($configs as $config) {
            $result[$config->config_key] = self::parseValue($config);
        }
        return $result;
    }

    /**
     * 获取配置列表（带分组信息和类型信息）
     * 返回格式: { groups: [...], configs: { group_name: [...] } }
     */
    public static function getConfigList(): array
    {
        // 获取分组信息
        $groupRows = \app\common\model\SystemConfigGroup::order('sort', 'asc')->order('id', 'asc')->select()->toArray();
        $groupMap = [];
        foreach ($groupRows as $g) {
            $groupMap[$g['name']] = [
                'id' => $g['id'],
                'name' => $g['name'],
                'label' => $g['label'],
                'icon' => $g['icon'],
                'description' => $g['description'],
                'sort' => $g['sort'],
            ];
        }

        // 获取配置项
        $configs = self::order('config_group', 'asc')->order('sort', 'asc')->order('id', 'asc')->select();
        $configGroups = [];
        foreach ($configs as $config) {
            $group = $config->config_group ?: 'basic';
            if (!isset($configGroups[$group])) {
                $configGroups[$group] = [];
            }
            $configGroups[$group][] = [
                'id' => $config->id,
                'key' => $config->config_key,
                'value' => self::parseValue($config),
                'type' => $config->config_type,
                'group' => $config->config_group,
                'description' => $config->description,
                'sort' => $config->sort,
                'extra' => $config->extra ? json_decode($config->extra, true) : null,
            ];
        }

        return [
            'groups' => array_values($groupMap),
            'configs' => $configGroups,
        ];
    }

    private static function parseValue($config)
    {
        $value = $config->config_value;
        switch ($config->config_type) {
            case 'switch':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? $value + 0 : $value;
            case 'checkbox':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }
}
