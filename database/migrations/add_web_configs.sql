-- ============================================
-- Web 前端展示用配置项
-- ============================================

-- 联系方式分组
INSERT IGNORE INTO `system_config_groups` (`name`, `label`, `icon`, `description`, `sort`) VALUES
('contact', '联系方式', 'Phone', '邮箱、QQ、微信、GitHub 等联系信息', 5),
('reward', '赞赏打赏', 'Money', '微信/支付宝收款码', 6),
('footer', '页脚配置', 'Document', '页脚文字、链接、备案等', 7);

-- 联系方式配置项
INSERT IGNORE INTO `system_configs` (`config_key`, `config_value`, `config_type`, `config_group`, `description`, `sort`) VALUES
('contact_email', '', 'text', 'contact', '联系邮箱', 1),
('contact_qq', '', 'text', 'contact', 'QQ号', 2),
('contact_qq_group', '', 'text', 'contact', 'QQ群号', 3),
('contact_wechat', '', 'text', 'contact', '微信号', 4),
('contact_wechat_qr', '', 'image', 'contact', '公众号二维码', 5),
('contact_github', '', 'text', 'contact', 'GitHub 地址', 6),
('contact_gitee', '', 'text', 'contact', 'Gitee 地址', 7),
('contact_bilibili', '', 'text', 'contact', 'B站主页地址', 8),
('contact_weibo', '', 'text', 'contact', '微博地址', 9),
('contact_douyin', '', 'text', 'contact', '抖音号', 10);

-- 赞赏打赏配置项
INSERT IGNORE INTO `system_configs` (`config_key`, `config_value`, `config_type`, `config_group`, `description`, `sort`) VALUES
('reward_enabled', '0', 'switch', 'reward', '开启打赏功能', 1),
('reward_text', '请作者喝杯咖啡 ☕', 'text', 'reward', '打赏提示文字', 2),
('reward_wechat_qr', '', 'image', 'reward', '微信收款码', 3),
('reward_alipay_qr', '', 'image', 'reward', '支付宝收款码', 4);

-- 页脚配置项
INSERT IGNORE INTO `system_configs` (`config_key`, `config_value`, `config_type`, `config_group`, `description`, `sort`) VALUES
('footer_text', '', 'textarea', 'footer', '页脚自定义文字', 1),
('footer_copyright', '', 'text', 'footer', '版权声明（如 © 2026 xxx）', 2),
('footer_links', '[]', 'textarea', 'footer', '页脚链接 JSON [{\"title\":\"\",\"url\":\"\"}]', 3),
('footer_beian', '', 'text', 'footer', '公安备案号（如 京ICP备xxx号）', 4);
