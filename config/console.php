<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'ai:generate' => \app\command\AiGenerate::class,
        'ai:consume'  => \app\command\AiConsume::class,
        'ai:publish'  => \app\command\AiPublish::class,
    ],
];
