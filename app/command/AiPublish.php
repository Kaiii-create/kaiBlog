<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\AiTask;
use app\common\model\AiLog;
use app\common\model\Article;
use app\common\model\ArticleTag;
use app\common\model\Tag;

class AiPublish extends Command
{
    protected function configure()
    {
        $this->setName('ai:publish')
            ->setDescription('发布已完成的AI生成任务为文章');
    }

    protected function execute(Input $input, Output $output)
    {
        $batch = 3;
        $tasks = AiTask::getReadyToPublish($batch);
        $published = 0;

        foreach ($tasks as $task) {
            try {
                $tags = array_filter(array_map('trim', explode(',', $task->tags ?? '')));

                $article = Article::create([
                    'category_id'     => $task->category_id,
                    'admin_id'        => 1,
                    'title'           => $task->title,
                    'content'         => $task->content,
                    'markdown'        => $task->markdown,
                    'summary'         => $task->summary,
                    'status'          => 1,
                    'seo_title'       => $task->seo_title,
                    'seo_keywords'    => $task->seo_keywords,
                    'seo_description' => $task->seo_description,
                    'published_at'    => date('Y-m-d H:i:s'),
                ]);

                foreach ($tags as $tagName) {
                    $tag = Tag::where('name', $tagName)->find();
                    if (!$tag) {
                        $tag = Tag::create(['name' => $tagName, 'slug' => $tagName]);
                    }
                    ArticleTag::create(['article_id' => $article->id, 'tag_id' => $tag->id]);
                    $tag->article_count = ArticleTag::where('tag_id', $tag->id)->count();
                    $tag->save();
                }

                $task->markPublished($article->id);

                if ($task->keyword_id) {
                    $keyword = $task->keyword;
                    $keyword->published_count += 1;
                    $keyword->save();
                }

                AiLog::log($task->id, $task->channel_id, 'publish', ['status' => 'success']);
                $published++;
                $output->writeln("[OK] Task #{$task->id} -> Article #{$article->id}: {$article->title}");

            } catch (\Exception $e) {
                AiLog::log($task->id, $task->channel_id, 'publish', [
                    'status' => 'failed', 'error_msg' => $e->getMessage(),
                ]);
                $output->writeln("[FAIL] Task #{$task->id}: {$e->getMessage()}");
            }
        }

        $output->writeln("Published: {$published}");
    }
}
