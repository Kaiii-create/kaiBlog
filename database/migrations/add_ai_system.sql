-- ============================================
-- AI 自动发文系统 - 6张表
-- ============================================

-- 1. AI 渠道配置
CREATE TABLE IF NOT EXISTS `ai_channels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '渠道名称',
  `type` varchar(50) NOT NULL COMMENT '渠道类型: deepseek/qianwen/wenxin/openai/claude',
  `api_key` varchar(500) NOT NULL COMMENT 'API Key',
  `api_url` varchar(500) DEFAULT NULL COMMENT '自定义API地址(留空用默认)',
  `model_name` varchar(100) DEFAULT NULL COMMENT '模型名称',
  `max_tokens` int(11) NOT NULL DEFAULT 2000 COMMENT '最大生成token数',
  `temperature` decimal(3,2) NOT NULL DEFAULT 0.70 COMMENT '生成温度(0-1)',
  `daily_quota` int(11) NOT NULL DEFAULT 100 COMMENT '每日调用额度',
  `daily_used` int(11) NOT NULL DEFAULT 0 COMMENT '今日已用',
  `daily_reset_at` date DEFAULT NULL COMMENT '额度重置日期',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态: 0禁用 1启用',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序(越小优先级越高)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status_sort` (`status`, `sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI渠道配置表';

-- 2. 提示词模板
CREATE TABLE IF NOT EXISTS `ai_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `type` varchar(30) NOT NULL DEFAULT 'article' COMMENT '类型: article/tutorial/summary',
  `prompt` text NOT NULL COMMENT '提示词模板(支持 {keyword} {category} {style} 变量)',
  `system_prompt` text DEFAULT NULL COMMENT '系统提示词',
  `description` varchar(255) DEFAULT NULL COMMENT '说明',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI提示词模板表';

-- 3. 关键词库
CREATE TABLE IF NOT EXISTS `ai_keywords` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(200) NOT NULL COMMENT '关键词',
  `category_id` bigint(20) unsigned DEFAULT NULL COMMENT '目标栏目ID',
  `template_id` bigint(20) unsigned DEFAULT NULL COMMENT '使用的提示词模板ID',
  `channel_id` bigint(20) unsigned DEFAULT NULL COMMENT '指定AI渠道(为空则自动选)',
  `article_count` int(11) NOT NULL DEFAULT 10 COMMENT '计划生成篇数',
  `generated_count` int(11) NOT NULL DEFAULT 0 COMMENT '已生成篇数',
  `published_count` int(11) NOT NULL DEFAULT 0 COMMENT '已发布篇数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态: 0暂停 1启用 2已完成',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI关键词库表';

-- 4. 生成任务队列
CREATE TABLE IF NOT EXISTS `ai_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `keyword_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联关键词ID',
  `channel_id` bigint(20) unsigned DEFAULT NULL COMMENT '使用的AI渠道ID',
  `template_id` bigint(20) unsigned DEFAULT NULL COMMENT '使用的模板ID',
  `category_id` bigint(20) unsigned DEFAULT NULL COMMENT '目标栏目ID',
  `prompt` text DEFAULT NULL COMMENT '实际发送的提示词',
  `title` varchar(200) DEFAULT NULL COMMENT '生成的文章标题',
  `content` longtext DEFAULT NULL COMMENT '生成的文章内容HTML',
  `markdown` longtext DEFAULT NULL COMMENT 'Markdown原文',
  `summary` varchar(500) DEFAULT NULL COMMENT '摘要',
  `tags` varchar(500) DEFAULT NULL COMMENT '标签(逗号分隔)',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/generating/completed/failed/published/dead',
  `error_msg` text DEFAULT NULL COMMENT '失败原因',
  `retry_count` int(11) NOT NULL DEFAULT 0 COMMENT '重试次数',
  `max_retry` int(11) NOT NULL DEFAULT 3 COMMENT '最大重试次数',
  `input_tokens` int(11) DEFAULT NULL COMMENT '输入token数',
  `output_tokens` int(11) DEFAULT NULL COMMENT '输出token数',
  `cost_time` int(11) DEFAULT NULL COMMENT '耗时(毫秒)',
  `scheduled_at` datetime DEFAULT NULL COMMENT '计划生成时间',
  `completed_at` datetime DEFAULT NULL COMMENT '完成时间',
  `published_at` datetime DEFAULT NULL COMMENT '发布时间',
  `article_id` bigint(20) unsigned DEFAULT NULL COMMENT '发布后的文章ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_keyword` (`keyword_id`),
  KEY `idx_scheduled` (`scheduled_at`),
  KEY `idx_channel` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI生成任务表';

-- 5. 执行日志
CREATE TABLE IF NOT EXISTS `ai_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联任务ID',
  `channel_id` bigint(20) unsigned DEFAULT NULL COMMENT '渠道ID',
  `action` varchar(30) NOT NULL COMMENT '操作: generate/publish/retry/error',
  `input_tokens` int(11) DEFAULT NULL,
  `output_tokens` int(11) DEFAULT NULL,
  `cost_time` int(11) DEFAULT NULL COMMENT '耗时(毫秒)',
  `status` varchar(20) NOT NULL DEFAULT 'success' COMMENT 'success/failed',
  `error_msg` text DEFAULT NULL,
  `extra` text DEFAULT NULL COMMENT '扩展信息JSON',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI执行日志表';

-- 6. 定时调度规则
CREATE TABLE IF NOT EXISTS `ai_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '规则名称',
  `cron_expr` varchar(50) NOT NULL COMMENT 'Cron表达式',
  `action` varchar(30) NOT NULL COMMENT '执行动作: generate/consume/publish',
  `config` text DEFAULT NULL COMMENT '配置JSON(如每次生成数量)',
  `last_run_at` datetime DEFAULT NULL COMMENT '上次执行时间',
  `next_run_at` datetime DEFAULT NULL COMMENT '下次执行时间',
  `run_count` int(11) NOT NULL DEFAULT 0 COMMENT '累计执行次数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态: 0禁用 1启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI定时调度规则表';

-- 插入默认提示词模板
INSERT INTO `ai_templates` (`name`, `type`, `prompt`, `system_prompt`, `description`) VALUES
('技术文章', 'article',
'请围绕关键词「{keyword}」写一篇技术文章。\n\n要求：\n1. 标题吸引人，包含关键词\n2. 内容详实，有代码示例\n3. 结构清晰：简介→正文→总结\n4. 字数1500-3000字\n5. 适合发布在技术博客\n\n所属分类：{category}\n文章风格：{style}\n\n请以JSON格式返回：\n{{"title":"文章标题","content":"HTML内容","summary":"100字摘要","tags":"标签1,标签2,标签3","seo_title":"SEO标题","seo_keywords":"关键词1,关键词2","seo_description":"SEO描述"}}',
'你是一个专业的技术博客作者，擅长写清晰、实用的技术文章。请用中文回答。',
'通用技术文章模板');

-- 插入默认调度规则
INSERT INTO `ai_schedules` (`name`, `cron_expr`, `action`, `config`, `status`) VALUES
('每日生成任务', '0 2 * * *', 'generate', '{"count": 10}', 1),
('每30分钟消费队列', '*/30 * * * *', 'consume', '{"batch": 5}', 1),
('每小时发布审核通过的文章', '0 * * * *', 'publish', '{"batch": 3}', 1);
