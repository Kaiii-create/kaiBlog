-- ============================================
-- 补充 SEO 全局配置项
-- ============================================

INSERT IGNORE INTO `system_configs` (`config_key`, `config_value`, `config_type`, `config_group`, `description`, `sort`, `extra`) VALUES
('seo_twitter_handle', '', 'text', 'seo', 'Twitter 账号 (@开头)', 10, NULL),
('seo_json_ld_enabled', '1', 'switch', 'seo', '启用 JSON-LD 结构化数据', 11, '{"label":"启用/关闭"}'),
('seo_baidu_submit_url', '', 'text', 'seo', '百度推送接口URL', 12, NULL),
('seo_canonical_enabled', '1', 'switch', 'seo', '启用 canonical 标签', 13, '{"label":"启用/关闭"}'),
('seo_og_locale', 'zh_CN', 'text', 'seo', 'OG Locale 语言标记', 14, NULL),
('seo_article_title_format', '{title} - {site_name}', 'text', 'seo', '文章标题格式 ({title}=文章标题, {site_name}=站点名)', 15, NULL),
('seo_tutorial_title_format', '{title} - {site_name}', 'text', 'seo', '教程标题格式', 16, NULL),
('seo_robots_custom', '', 'textarea', 'seo', 'robots.txt 自定义规则 (追加)', 17, NULL)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);
