-- ============================================
-- 插入AI模块菜单到 permissions 表
-- 执行时间：2026-05-11
-- ============================================

USE `kaiii_top`;

-- 1. 插入AI内容生成（一级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI内容生成', 'ai', 'Promotion', '', 0, 1, 680, 1, NOW(), NOW());

-- 获取刚才插入的一级菜单ID（应该是68）
SET @ai_parent_id = LAST_INSERT_ID();

-- 2. 插入AI渠道管理（二级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI渠道管理', 'ai.channels', 'Connection', '/admin/ai/channels', @ai_parent_id, 1, 681, 1, NOW(), NOW());

-- 3. 插入AI关键词（二级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI关键词', 'ai.keywords', 'PriceTag', '/admin/ai/keywords', @ai_parent_id, 1, 682, 1, NOW(), NOW());

-- 4. 插入AI提示词模板（二级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI提示词模板', 'ai.templates', 'Document', '/admin/ai/templates', @ai_parent_id, 1, 683, 1, NOW(), NOW());

-- 5. 插入AI生成任务（二级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI生成任务', 'ai.tasks', 'Timer', '/admin/ai/tasks', @ai_parent_id, 1, 684, 1, NOW(), NOW());

-- 6. 插入AI调用日志（二级菜单）
INSERT INTO `permissions` (`name`, `slug`, `icon`, `route_path`, `parent_id`, `type`, `sort`, `status`, `created_at`, `updated_at`) 
VALUES ('AI调用日志', 'ai.logs', 'Notebook', '/admin/ai/logs', @ai_parent_id, 1, 685, 1, NOW(), NOW());

-- ============================================
-- 为超级管理员角色（role_id=1）分配AI菜单权限
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT 1, id FROM `permissions` WHERE `slug` LIKE 'ai%' AND `type` = 1;

-- ============================================
-- 验证：查看插入的AI菜单
-- ============================================
-- SELECT * FROM `permissions` WHERE `slug` LIKE 'ai%' ORDER BY id;
-- SELECT p.* FROM `permissions` p JOIN `role_permissions` rp ON p.id = rp.permission_id WHERE rp.role_id = 1 AND p.`slug` LIKE 'ai%' ORDER BY p.id;
