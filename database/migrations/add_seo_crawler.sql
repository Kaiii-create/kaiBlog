-- ============================================
-- SEO 增强 + 爬虫记录表
-- 执行时间: 2026-05-09
-- ============================================

-- 1. 教程章节表增加 SEO 字段
ALTER TABLE `tutorial_chapters`
  ADD COLUMN `seo_title` varchar(255) DEFAULT NULL COMMENT 'SEO标题' AFTER `view_count`,
  ADD COLUMN `seo_keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词' AFTER `seo_title`,
  ADD COLUMN `seo_description` varchar(500) DEFAULT NULL COMMENT 'SEO描述' AFTER `seo_keywords`;

-- 2. 爬虫访问记录表
CREATE TABLE IF NOT EXISTS `crawler_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bot_type` varchar(50) NOT NULL COMMENT '爬虫类型: google, baidu, bing, yandex, sogou, bytespider, unknown',
  `bot_name` varchar(100) DEFAULT NULL COMMENT '爬虫完整名称',
  `user_agent` varchar(500) NOT NULL COMMENT 'User-Agent',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `request_url` varchar(2000) NOT NULL COMMENT '请求URL路径',
  `request_method` varchar(10) DEFAULT 'GET' COMMENT '请求方法',
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP响应状态码',
  `response_time` int(11) DEFAULT NULL COMMENT '响应耗时(毫秒)',
  `referer` varchar(2000) DEFAULT NULL COMMENT '来源页',
  `country` varchar(50) DEFAULT NULL COMMENT '国家/地区',
  `is_mobile` tinyint(1) DEFAULT 0 COMMENT '是否移动端爬虫',
  `created_at` datetime(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '爬取时间',
  PRIMARY KEY (`id`),
  KEY `idx_bot_type` (`bot_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_request_url` (`request_url`(191)),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='爬虫访问记录表';

-- 3. 爬虫每日统计表（聚合用，方便后台展示）
CREATE TABLE IF NOT EXISTS `crawler_daily_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL COMMENT '统计日期',
  `bot_type` varchar(50) NOT NULL COMMENT '爬虫类型',
  `crawl_count` int(11) NOT NULL DEFAULT 0 COMMENT '爬取次数',
  `unique_pages` int(11) NOT NULL DEFAULT 0 COMMENT '独立页面数',
  `error_count` int(11) NOT NULL DEFAULT 0 COMMENT '错误次数(4xx/5xx)',
  `last_crawl_at` datetime(3) DEFAULT NULL COMMENT '最后爬取时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date_bot` (`stat_date`, `bot_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='爬虫每日统计表';
