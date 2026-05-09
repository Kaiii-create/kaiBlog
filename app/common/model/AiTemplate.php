<?php
namespace app\common\model;

use think\Model;

class AiTemplate extends Model
{
    protected $table = 'ai_templates';

    /**
     * 渲染提示词（替换变量）
     */
    public function render(array $vars): string
    {
        $prompt = $this->prompt;
        foreach ($vars as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        return $prompt;
    }
}
