<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\Article;
use app\common\model\Tutorial;
use app\common\model\TutorialChapter;
use app\common\model\Category;
use app\common\model\SystemConfig;

class SeoController extends BaseController
{
    use ApiResponse;
    /**
     * Sitemap XML
     * GET /sitemap.xml
     */
    public function sitemap()
    {
        $siteUrl = SystemConfig::getConfig('seo_site_url', 'http://www.kaiii.top');
        $siteUrl = rtrim($siteUrl, '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // 首页
        $xml .= $this->url($siteUrl . '/', '1.0', 'daily');

        // 文章
        $articles = Article::where('status', 1)->where('published_at', '>', '2020-01-01')
            ->order('published_at', 'desc')->select();
        foreach ($articles as $a) {
            $xml .= $this->url(
                $siteUrl . '/articles/' . ($a->slug ?: $a->id),
                '0.8',
                'weekly',
                $a->updated_at
            );
        }

        // 教程
        $tutorials = Tutorial::where('status', 1)->select();
        foreach ($tutorials as $t) {
            $xml .= $this->url(
                $siteUrl . '/tutorials/' . ($t->slug ?: $t->id),
                '0.7',
                'weekly',
                $t->updated_at
            );

            // 教程章节
            $chapters = TutorialChapter::where('tutorial_id', $t->id)
                ->where('status', 1)
                ->where('content', '<>', '')
                ->select();
            foreach ($chapters as $ch) {
                $xml .= $this->url(
                    $siteUrl . '/tutorials/' . ($t->slug ?: $t->id) . '/chapter/' . $ch->id,
                    '0.6',
                    'monthly',
                    $ch->updated_at
                );
            }
        }

        // 栏目
        $categories = Category::where('status', 1)->select();
        foreach ($categories as $c) {
            $xml .= $this->url(
                $siteUrl . '/categories/' . ($c->slug ?: $c->id),
                '0.6',
                'weekly',
                $c->updated_at
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header([
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function url(string $loc, string $priority = '0.5', string $changefreq = 'monthly', string $lastmod = null): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        if ($lastmod) {
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        }
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";
        return $xml;
    }

    /**
     * robots.txt
     * GET /robots.txt
     */
    public function robots()
    {
        $siteUrl = SystemConfig::getConfig('seo_site_url', 'http://www.kaiii.top');
        $customRules = SystemConfig::getConfig('seo_robots_custom', '');

        $txt = "User-agent: *\n";
        $txt .= "Allow: /\n";
        $txt .= "Disallow: /admin/\n";
        $txt .= "Disallow: /api/\n";
        $txt .= "Disallow: /runtime/\n";
        $txt .= "Disallow: /vendor/\n";
        $txt .= "Disallow: /think/\n";
        if ($customRules) {
            $txt .= "\n" . trim($customRules) . "\n";
        }
        $txt .= "\n";
        $txt .= "Sitemap: {$siteUrl}/sitemap.xml\n";

        return response($txt, 200)->header([
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * 页面 SEO 信息
     * GET /api/seo/:type/:id
     */
    public function info($type, $id = null)
    {
        $siteUrl = SystemConfig::getConfig('seo_site_url', 'http://www.kaiii.top');
        $siteUrl = rtrim($siteUrl, '/');

        switch ($type) {
            case 'home':
                return $this->success([
                    'title' => SystemConfig::getConfig('seo_home_title', SystemConfig::getConfig('site_name', '博客')),
                    'description' => SystemConfig::getConfig('seo_home_description', ''),
                    'keywords' => SystemConfig::getConfig('seo_home_keywords', ''),
                    'og_image' => SystemConfig::getConfig('seo_og_image', ''),
                    'canonical_url' => $siteUrl . '/',
                ]);

            case 'article':
                $article = Article::with(['category', 'admin', 'tags'])->where('id', $id)->where('status', 1)->find();
                if (!$article) return $this->error('文章不存在', 404);
                $tagNames = $article->tags ? $article->tags->column('name') : [];
                return $this->success([
                    'title' => ($article->seo_title ?: $article->title) . ' - ' . SystemConfig::getConfig('site_name', ''),
                    'description' => $article->seo_description ?: $article->summary ?: mb_substr(strip_tags($article->content), 0, 160),
                    'keywords' => $article->seo_keywords ?: implode(',', $tagNames),
                    'og_title' => $article->seo_title ?: $article->title,
                    'og_description' => $article->seo_description ?: $article->summary ?: mb_substr(strip_tags($article->content), 0, 200),
                    'og_image' => $article->cover ?: SystemConfig::getConfig('seo_og_image', ''),
                    'og_type' => 'article',
                    'twitter_card' => $article->cover ? 'summary_large_image' : 'summary',
                    'canonical_url' => $siteUrl . '/articles/' . ($article->slug ?: $article->id),
                    'json_ld' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Article',
                        'headline' => $article->title,
                        'description' => $article->summary ?: mb_substr(strip_tags($article->content), 0, 160),
                        'datePublished' => $article->published_at,
                        'dateModified' => $article->updated_at,
                        'author' => [
                            '@type' => 'Person',
                            'name' => $article->admin ? $article->admin->nickname : 'Admin',
                        ],
                        'publisher' => [
                            '@type' => 'Organization',
                            'name' => SystemConfig::getConfig('site_name', 'Blog'),
                            'logo' => ['@type' => 'ImageObject', 'url' => SystemConfig::getConfig('site_logo', '')],
                        ],
                        'image' => $article->cover ? [$article->cover] : [],
                        'mainEntityOfPage' => $siteUrl . '/articles/' . ($article->slug ?: $article->id),
                        'articleSection' => $article->category ? $article->category->name : '',
                        'keywords' => implode(',', $tagNames),
                    ],
                ]);

            case 'category':
                $category = Category::where('id', $id)->where('status', 1)->find();
                if (!$category) return $this->error('栏目不存在', 404);
                return $this->success([
                    'title' => $category->seo_title ?: $category->name,
                    'description' => $category->seo_description ?: $category->description,
                    'keywords' => $category->seo_keywords ?: '',
                    'og_image' => $category->cover ?: SystemConfig::getConfig('seo_og_image', ''),
                    'canonical_url' => $siteUrl . '/categories/' . ($category->slug ?: $category->id),
                    'json_ld' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'CollectionPage',
                        'name' => $category->name,
                        'description' => $category->description,
                    ],
                ]);

            case 'chapter':
                $chapter = TutorialChapter::where('id', $id)->where('status', 1)->find();
                if (!$chapter) return $this->error('章节不存在', 404);
                $tutorial = Tutorial::where('id', $chapter->tutorial_id)->find();
                $chapterTitle = $chapter->seo_title ?: $chapter->title;
                $fullTitle = $tutorial ? ($chapterTitle . ' - ' . $tutorial->title) : $chapterTitle;
                $tutorialSlug = $tutorial ? ($tutorial->slug ?: $tutorial->id) : $chapter->tutorial_id;
                return $this->success([
                    'title' => $fullTitle,
                    'description' => $chapter->seo_description ?: $chapter->summary ?: mb_substr(strip_tags($chapter->content ?? ''), 0, 160),
                    'keywords' => $chapter->seo_keywords ?: ($tutorial->seo_keywords ?? ''),
                    'og_image' => $tutorial->cover ?? SystemConfig::getConfig('seo_og_image', ''),
                    'canonical_url' => $siteUrl . '/tutorials/' . $tutorialSlug . '/chapter/' . $chapter->id,
                    'json_ld' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'TechArticle',
                        'headline' => $chapter->title,
                        'isPartOf' => [
                            '@type' => 'Course',
                            'name' => $tutorial->title ?? '',
                        ],
                        'datePublished' => $chapter->published_at,
                        'dateModified' => $chapter->updated_at,
                    ],
                ]);

            case 'tutorial':
                $tutorial = Tutorial::where('id', $id)->where('status', 1)->find();
                if (!$tutorial) return $this->error('教程不存在', 404);
                return $this->success([
                    'title' => $tutorial->seo_title ?: $tutorial->title,
                    'description' => $tutorial->seo_description ?: $tutorial->description,
                    'keywords' => $tutorial->seo_keywords ?: '',
                    'og_image' => $tutorial->cover ?: SystemConfig::getConfig('seo_og_image', ''),
                    'canonical_url' => $siteUrl . '/tutorials/' . ($tutorial->slug ?: $tutorial->id),
                    'json_ld' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Course',
                        'name' => $tutorial->title,
                        'description' => $tutorial->description,
                        'provider' => ['@type' => 'Organization', 'name' => SystemConfig::getConfig('site_name', 'Blog')],
                    ],
                ]);

            default:
                return $this->error('类型不支持');
        }
    }
}
