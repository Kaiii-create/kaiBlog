-- ============================================
-- 系统配置分组表
-- ============================================

CREATE TABLE IF NOT EXISTS `system_config_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '分组标识(英文)',
  `label` varchar(100) NOT NULL COMMENT '分组显示名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标标识',
  `description` varchar(255) DEFAULT NULL COMMENT '分组说明',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置分组表';

-- 迁移已有分组数据
INSERT IGNORE INTO `system_config_groups` (`name`, `label`, `icon`, `description`, `sort`) VALUES
('basic', '基础配置', 'Setting', '网站名称、Logo、公告等基础信息', 1),
('seo', 'SEO 配置', 'Search', '搜索引擎优化相关配置', 2),
('upload', '上传配置', 'Upload', '文件上传大小、类型限制', 3),
('user', '用户配置', 'User', '注册、评论等用户相关配置', 4);
