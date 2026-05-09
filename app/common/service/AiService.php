<?php
namespace app\common\service;

use app\common\model\AiChannel;
use app\common\model\AiLog;
use app\common\model\AiTask;
use app\common\model\AiKeyword;
use app\common\model\AiTemplate;

class AiService
{
    /**
     * 调用 AI 生成内容
     * @param AiChannel $channel AI渠道
     * @param string $prompt 提示词
     * @param string|null $systemPrompt 系统提示词
     * @return array ['title', 'content', 'summary', 'tags', 'seo_title', 'seo_keywords', 'seo_description', 'input_tokens', 'output_tokens', 'cost_time']
     * @throws \Exception
     */
    public static function generate(AiChannel $channel, string $prompt, ?string $systemPrompt = null): array
    {
        $startTime = microtime(true);

        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $result = self::callApi($channel, $messages);

        $costTime = intval((microtime(true) - $startTime) * 1000);

        // 解析返回内容
        $parsed = self::parseResponse($result['content']);
        $parsed['input_tokens'] = $result['input_tokens'] ?? 0;
        $parsed['output_tokens'] = $result['output_tokens'] ?? 0;
        $parsed['cost_time'] = $costTime;

        // 消耗额度
        $channel->consume();

        return $parsed;
    }

    /**
     * 统一 API 调用
     */
    private static function callApi(AiChannel $channel, array $messages): array
    {
        $type = $channel->type;
        $apiUrl = $channel->getApiUrl();
        $apiKey = $channel->api_key;
        $model = $channel->getModelName();

        switch ($type) {
            case 'deepseek':
            case 'openai':
            case 'qianwen':
                return self::callOpenAICompatible($apiUrl, $apiKey, $model, $messages, $channel->max_tokens, (float)$channel->temperature);

            case 'wenxin':
                return self::callWenxin($apiKey, $messages, $channel->max_tokens);

            default:
                // 默认走 OpenAI 兼容协议
                return self::callOpenAICompatible($apiUrl, $apiKey, $model, $messages, $channel->max_tokens, (float)$channel->temperature);
        }
    }

    /**
     * OpenAI 兼容协议调用（DeepSeek/OpenAI/Qianwen 通用）
     */
    private static function callOpenAICompatible(string $url, string $apiKey, string $model, array $messages, int $maxTokens, float $temperature): array
    {
        $postData = [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("API请求失败: {$error}");
        }

        $data = json_decode($response, true);
        if (!$data) {
            throw new \Exception("API响应解析失败: " . substr($response, 0, 200));
        }

        if ($httpCode !== 200) {
            $errMsg = $data['error']['message'] ?? $data['message'] ?? '未知错误';
            throw new \Exception("API错误 (HTTP {$httpCode}): {$errMsg}");
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        if (empty($content)) {
            throw new \Exception("API返回内容为空");
        }

        return [
            'content'      => $content,
            'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'output_tokens'=> $data['usage']['completion_tokens'] ?? 0,
        ];
    }

    /**
     * 文心一言调用
     */
    private static function callWenxin(string $apiKey, array $messages, int $maxTokens): array
    {
        // 文心一言需要先获取 access_token
        // 这里简化处理，实际需要根据百度的鉴权方式实现
        $parts = explode(':', $apiKey);
        if (count($parts) !== 2) {
            throw new \Exception("文心一言API Key格式应为 client_id:client_secret");
        }

        $accessToken = self::getWenxinToken($parts[0], $parts[1]);

        $url = "https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/ernie-4.0-8k?access_token={$accessToken}";

        $postData = [
            'messages'    => $messages,
            'max_output_tokens' => $maxTokens,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data || $httpCode !== 200) {
            throw new \Exception("文心API错误: " . ($data['error_msg'] ?? $response));
        }

        return [
            'content'      => $data['result'] ?? '',
            'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'output_tokens'=> $data['usage']['completion_tokens'] ?? 0,
        ];
    }

    /**
     * 获取文心一言 access_token
     */
    private static function getWenxinToken(string $clientId, string $clientSecret): string
    {
        $url = "https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id={$clientId}&client_secret={$clientSecret}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new \Exception("获取文心access_token失败");
        }
        return $data['access_token'];
    }

    /**
     * 解析 AI 返回的 JSON 内容
     */
    private static function parseResponse(string $content): array
    {
        $default = [
            'title'         => '未命名文章',
            'content'       => '',
            'summary'       => '',
            'tags'          => '',
            'seo_title'     => '',
            'seo_keywords'  => '',
            'seo_description' => '',
        ];

        // 尝试提取 JSON（可能被 markdown 代码块包裹）
        $jsonStr = $content;

        // 移除 markdown 代码块标记
        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?```/s', $content, $m)) {
            $jsonStr = $m[1];
        }

        $parsed = json_decode(trim($jsonStr), true);
        if (is_array($parsed)) {
            return array_merge($default, $parsed);
        }

        // JSON 解析失败，尝试从纯文本中提取
        if (preg_match('/"title"\s*:\s*"([^"]+)"/', $content, $m)) {
            $default['title'] = $m[1];
        }

        // 将整个内容作为文章内容
        $default['content'] = self::markdownToHtml($content);
        $default['summary'] = mb_substr(strip_tags($default['content']), 0, 160);

        return $default;
    }

    /**
     * 简易 Markdown 转 HTML
     */
    private static function markdownToHtml(string $md): string
    {
        // 标题
        $md = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $md);
        $md = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $md);
        $md = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $md);
        // 代码块
        $md = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code class="language-$1">$2</code></pre>', $md);
        // 行内代码
        $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md);
        // 加粗/斜体
        $md = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $md);
        $md = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $md);
        // 列表
        $md = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $md);
        // 段落
        $md = preg_replace('/\n\n/', '</p><p>', $md);
        $md = '<p>' . $md . '</p>';
        $md = str_replace('<p></p>', '', $md);

        return $md;
    }
}
