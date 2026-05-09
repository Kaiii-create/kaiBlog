<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\AiTask;
use app\common\model\AiChannel;
use app\common\model\AiTemplate;
use app\common\model\AiLog;
use app\common\service\AiService;

class AiConsume extends Command
{
    protected function configure()
    {
        $this->setName('ai:consume')
            ->setDescription('消费AI生成任务队列');
    }

    protected function execute(Input $input, Output $output)
    {
        $batch = 5;
        $tasks = AiTask::getPending($batch);
        $success = 0;
        $failed = 0;

        foreach ($tasks as $task) {
            $task->markGenerating();

            try {
                $channel = $task->channel_id
                    ? AiChannel::getAvailableById($task->channel_id)
                    : AiChannel::getAvailable();

                if (!$channel) {
                    throw new \Exception('没有可用的AI渠道或额度已用完');
                }

                $template = $task->template_id
                    ? AiTemplate::find($task->template_id)
                    : AiTemplate::where('status', 1)->order('id', 'asc')->find();

                $category = $task->category_id
                    ? \app\common\model\Category::find($task->category_id)
                    : null;

                $keyword = $task->keyword ? $task->keyword->keyword : '技术文章';

                $vars = [
                    'keyword'  => $keyword,
                    'category' => $category ? $category->name : '未分类',
                    'style'    => '技术博客',
                ];

                $prompt = $template ? $template->render($vars) : "请围绕「{$keyword}」写一篇技术文章，返回JSON格式。";
                $systemPrompt = $template ? $template->system_prompt : null;

                $task->prompt = $prompt;
                $task->channel_id = $channel->id;
                $task->save();

                $result = AiService::generate($channel, $prompt, $systemPrompt);
                $task->markCompleted($result);

                AiLog::log($task->id, $channel->id, 'generate', [
                    'input_tokens'  => $result['input_tokens'],
                    'output_tokens' => $result['output_tokens'],
                    'cost_time'     => $result['cost_time'],
                    'status'        => 'success',
                ]);

                if ($task->keyword_id) {
                    $task->keyword->markGenerated();
                }

                $success++;
                $output->writeln("[OK] Task #{$task->id}: {$result['title']}");

            } catch (\Exception $e) {
                $task->markFailed($e->getMessage());

                AiLog::log($task->id, $task->channel_id, 'error', [
                    'status'    => 'failed',
                    'error_msg' => $e->getMessage(),
                ]);

                $failed++;
                $output->writeln("[FAIL] Task #{$task->id}: {$e->getMessage()}");
            }
        }

        $output->writeln("Done. Success: {$success}, Failed: {$failed}");
    }
}
