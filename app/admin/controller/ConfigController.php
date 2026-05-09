<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\SystemConfig;
use app\common\model\SystemConfigGroup;

class ConfigController extends BaseController
{
    use ApiResponse;

    /**
     * 获取所有配置（带分组）
     * GET /api/admin/config
     */
    public function index()
    {
        $groups = SystemConfig::getConfigList();
        return $this->success($groups);
    }

    /**
     * 批量更新配置
     * PUT /api/admin/config
     */
    public function update()
    {
        $data = $this->request->put();
        if (empty($data['configs'])) return $this->error('参数错误');

        foreach ($data['configs'] as $item) {
            if (empty($item['key'])) continue;
            SystemConfig::where('config_key', $item['key'])->update([
                'config_value' => $item['value'] ?? '',
            ]);
        }

        return $this->success(null, '更新成功');
    }

    /**
     * 新增配置项
     * POST /api/admin/config
     */
    public function save()
    {
        $data = $this->request->post();
        if (empty($data['key']) || empty($data['description'])) {
            return $this->error('key 和 description 不能为空');
        }

        $exist = SystemConfig::where('config_key', $data['key'])->find();
        if ($exist) return $this->error('配置key已存在');

        $config = SystemConfig::create([
            'config_key' => $data['key'],
            'config_value' => $data['value'] ?? '',
            'config_type' => $data['type'] ?? 'text',
            'config_group' => $data['group'] ?? 'basic',
            'description' => $data['description'],
            'sort' => $data['sort'] ?? 0,
            'extra' => !empty($data['extra']) ? json_encode($data['extra'], JSON_UNESCAPED_UNICODE) : null,
        ]);

        return $this->success($config, '创建成功');
    }

    /**
     * 删除配置项
     * DELETE /api/admin/config/:id
     */
    public function delete($id)
    {
        $config = SystemConfig::find($id);
        if (!$config) return $this->error('配置不存在', 404);
        $config->delete();
        return $this->success(null, '删除成功');
    }

    // ============================================
    // 配置分组管理
    // ============================================

    /**
     * 分组列表（含配置项数量）
     * GET /api/admin/config-groups
     */
    public function groupIndex()
    {
        $groups = SystemConfigGroup::getAllWithCount();
        return $this->success($groups);
    }

    /**
     * 分组详情（含配置项列表）
     * GET /api/admin/config-groups/:id
     */
    public function groupRead($id)
    {
        $group = SystemConfigGroup::with('configs')->find($id);
        if (!$group) return $this->error('分组不存在', 404);
        return $this->success($group);
    }

    /**
     * 新增分组
     * POST /api/admin/config-groups
     */
    public function groupSave()
    {
        $data = $this->request->post();
        $rules = [
            'name' => 'require|alphaDash|max:50|unique:system_config_groups',
            'label' => 'require|max:100',
        ];
        $messages = [
            'name.require' => '请输入分组标识',
            'name.alphaDash' => '标识只能包含字母、数字、下划线和破折号',
            'name.unique' => '分组标识已存在',
            'label.require' => '请输入分组名称',
        ];
        $validate = new \think\Validate($rules, $messages);
        if (!$validate->check($data)) return $this->error($validate->getError());

        $group = SystemConfigGroup::create([
            'name' => $data['name'],
            'label' => $data['label'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 0,
        ]);

        return $this->success($group, '创建成功');
    }

    /**
     * 更新分组
     * PUT /api/admin/config-groups/:id
     */
    public function groupUpdate($id)
    {
        $group = SystemConfigGroup::find($id);
        if (!$group) return $this->error('分组不存在', 404);

        $data = $this->request->put();
        $oldName = $group->name;

        // 如果改了 name，同步更新 system_configs 里的 config_group
        if (!empty($data['name']) && $data['name'] !== $oldName) {
            $rules = ['name' => 'alphaDash|max:50|unique:system_config_groups,name,' . $id];
            $validate = new \think\Validate($rules);
            if (!$validate->check($data)) return $this->error($validate->getError());

            // 更新关联的配置项分组名
            SystemConfig::where('config_group', $oldName)->update([
                'config_group' => $data['name'],
            ]);
        }

        $group->save($data);
        return $this->success($group, '更新成功');
    }

    /**
     * 删除分组
     * DELETE /api/admin/config-groups/:id
     */
    public function groupDelete($id)
    {
        $group = SystemConfigGroup::find($id);
        if (!$group) return $this->error('分组不存在', 404);

        // 检查是否有配置项属于此分组
        $count = SystemConfig::where('config_group', $group->name)->count();
        if ($count > 0) {
            return $this->error("该分组下还有 {$count} 个配置项，请先移除或转移");
        }

        $group->delete();
        return $this->success(null, '删除成功');
    }

    /**
     * 分组排序
     * PUT /api/admin/config-groups/sort
     */
    public function groupSort()
    {
        $data = $this->request->put();
        if (empty($data['sorts'])) return $this->error('参数错误');

        foreach ($data['sorts'] as $item) {
            SystemConfigGroup::where('id', $item['id'])->update(['sort' => $item['sort']]);
        }
        return $this->success(null, '排序成功');
    }
}
