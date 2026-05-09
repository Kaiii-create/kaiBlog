<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\AiKeyword;
use app\common\model\AiTask;

class AiGenerate extends Command
{
    protected function configure()
    {
        $this->setName('ai:generate')
            ->setDescription('根据关键词库生成AI任务队列');
    }

    protected function execute(Input $input, Output $output)
    {
        $count = 10; // 每个关键词生成的任务数

        $keywords = AiKeyword::getPending();
        $created = 0;

        foreach ($keywords as $keyword) {
            for ($i = 0; $i < $count; $i++) {
                if ($keyword->generated_count + $created >= $keyword->article_count) break;

                AiTask::create([
                    'keyword_id'  => $keyword->id,
                    'channel_id'  => $keyword->channel_id,
                    'template_id' => $keyword->template_id,
                    'category_id' => $keyword->category_id,
                    'status'      => 'pending',
                ]);
                $created++;
            }
        }

        $output->writeln("Created {$created} tasks for {$keywords->count()} keywords");
    }
}
