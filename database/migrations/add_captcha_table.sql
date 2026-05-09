CREATE TABLE IF NOT EXISTS `captcha_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `captcha_key` varchar(64) NOT NULL COMMENT '验证码key',
  `captcha_code` varchar(10) NOT NULL COMMENT '验证码(小写)',
  `expired_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_key` (`captcha_key`),
  KEY `idx_expired` (`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='验证码存储表';
