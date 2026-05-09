<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 基础模型 - 修复 SoftDelete 的 datetime 格式问题
 */
class BaseModel extends Model
{
    use SoftDelete;

    protected $deleteTime = 'deleted_at';
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 重写删除方法，确保 deleted_at 使用 datetime 格式
     */
    public function delete()
    {
        if ($this->getDeleteTimeField()) {
            $this->data($this->getDeleteTimeField(), date($this->getDateFormat()));
            $this->update();
            return true;
        }
        return parent::delete();
    }
}
