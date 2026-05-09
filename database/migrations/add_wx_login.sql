-- ============================================
-- 微信公众号扫码登录
-- ============================================

-- 微信登录会话表
CREATE TABLE IF NOT EXISTS `wx_login_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_key` varchar(64) NOT NULL COMMENT '动态码(UUID)',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/scanned/confirmed/expired',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '登录成功后关联的用户ID',
  `openid` varchar(100) DEFAULT NULL COMMENT '微信openid',
  `expired_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session_key` (`session_key`),
  KEY `idx_status` (`status`),
  KEY `idx_expired` (`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信登录会话表';

-- 微信用户绑定表
CREATE TABLE IF NOT EXISTS `wx_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联本地用户ID(NULL=未绑定)',
  `openid` varchar(100) NOT NULL COMMENT '微信openid',
  `subscribe` tinyint(1) DEFAULT 0 COMMENT '是否关注公众号',
  `subscribe_at` datetime DEFAULT NULL COMMENT '关注时间',
  `unsubscribe_at` datetime DEFAULT NULL COMMENT '取关时间',
  `last_login_at` datetime DEFAULT NULL COMMENT '最后登录时间',
  `login_count` int(11) NOT NULL DEFAULT 0 COMMENT '登录次数',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信用户绑定表';
