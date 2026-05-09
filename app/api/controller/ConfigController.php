<?php
namespace app\api\controller;

use app\BaseController;
use app\common\trait_\ApiResponse;
use app\common\model\SystemConfig;

class ConfigController extends BaseController
{
    use ApiResponse;

    /**
     * 获取公开配置（结构化，供 Web 前端渲染）
     * GET /api/config
     *
     * 返回按用途分组的配置，前端直接渲染
     */
    public function index()
    {
        $keys = [
            // 站点基础
            'site_name', 'site_description', 'site_keywords', 'site_logo',
            'site_status', 'site_notice',
            // 联系方式
            'contact_email', 'contact_qq', 'contact_qq_group',
            'contact_wechat', 'contact_wechat_qr',
            'contact_github', 'contact_gitee', 'contact_bilibili',
            'contact_weibo', 'contact_douyin',
            // 打赏
            'reward_enabled', 'reward_text', 'reward_wechat_qr', 'reward_alipay_qr',
            // 页脚
            'footer_text', 'footer_copyright', 'footer_links', 'footer_beian',
            // SEO 前端需要
            'seo_home_title', 'seo_home_description', 'seo_home_keywords',
            'seo_og_image', 'seo_site_url',
            'seo_google_analytics', 'seo_custom_head',
            // ICP
            'icp_number',
            // 社交链接（旧字段，兼容）
            'social_links',
        ];

        $all = SystemConfig::getConfigs($keys);

        // 解析 JSON 字段
        $socialLinks = $this->parseJson($all['social_links'] ?? '{}');
        $footerLinks = $this->parseJson($all['footer_links'] ?? '[]');

        // 组装结构化响应
        return $this->success([
            // 站点信息
            'site' => [
                'name'        => $all['site_name'] ?? '',
                'description' => $all['site_description'] ?? '',
                'keywords'    => $all['site_keywords'] ?? '',
                'logo'        => $all['site_logo'] ?? '',
                'status'      => ($all['site_status'] ?? '1') == '1',
                'notice'      => $all['site_notice'] ?? '',
            ],

            // 联系方式
            'contact' => [
                'email'       => $all['contact_email'] ?? '',
                'qq'          => $all['contact_qq'] ?? '',
                'qq_group'    => $all['contact_qq_group'] ?? '',
                'wechat'      => $all['contact_wechat'] ?? '',
                'wechat_qr'   => $all['contact_wechat_qr'] ?? '',
                'github'      => $all['contact_github'] ?? '',
                'gitee'       => $all['contact_gitee'] ?? '',
                'bilibili'    => $all['contact_bilibili'] ?? '',
                'weibo'       => $all['contact_weibo'] ?? '',
                'douyin'      => $all['contact_douyin'] ?? '',
            ],

            // 打赏
            'reward' => [
                'enabled'    => ($all['reward_enabled'] ?? '0') == '1',
                'text'       => $all['reward_text'] ?? '请作者喝杯咖啡 ☕',
                'wechat_qr'  => $all['reward_wechat_qr'] ?? '',
                'alipay_qr'  => $all['reward_alipay_qr'] ?? '',
            ],

            // 页脚
            'footer' => [
                'text'      => $all['footer_text'] ?? '',
                'copyright' => $all['footer_copyright'] ?? '',
                'links'     => $footerLinks,
                'beian'     => $all['footer_beian'] ?? '',
                'icp'       => $all['icp_number'] ?? '',
            ],

            // 社交链接（兼容旧字段 + 新字段合并）
            'social' => $socialLinks,

            // 微信登录
            'wx_login' => [
                'enabled' => true,
                'app_id'  => 'wxed57e35f1d6a2e91',
            ],

            // SEO（前端 head 渲染用）
            'seo' => [
                'home_title'       => $all['seo_home_title'] ?? '',
                'home_description' => $all['seo_home_description'] ?? '',
                'home_keywords'    => $all['seo_home_keywords'] ?? '',
                'og_image'         => $all['seo_og_image'] ?? '',
                'site_url'         => $all['seo_site_url'] ?? '',
                'google_analytics' => $all['seo_google_analytics'] ?? '',
                'custom_head'      => $all['seo_custom_head'] ?? '',
            ],
        ]);
    }

    /**
     * 安全解析 JSON
     */
    private function parseJson(string $value, $default = null)
    {
        $decoded = json_decode($value, true);
        return $decoded ?? $default;
    }
}
