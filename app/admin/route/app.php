<?php
use think\facade\Route;

// ============ 无需鉴权 ============
Route::post('auth/login', 'Auth/login');

// ============ 需要管理员鉴权 ============
Route::group('', function () {
    // 管理员信息
    Route::get('auth/profile', 'Auth/profile');

    // 控制台
    Route::get('dashboard', 'Dashboard/index');

    // 管理员管理
    Route::resource('admins', 'AdminController');

    // 角色管理（特定路由在前）
    Route::put('roles/:id/permissions', 'RoleController/assignPermissions');
    Route::resource('roles', 'RoleController');

    // 权限管理
    Route::resource('permissions', 'PermissionController');

    // 栏目管理
    Route::resource('categories', 'CategoryController');

    // 文章管理
    Route::resource('articles', 'ArticleController');

    // 标签管理
    Route::resource('tags', 'TagController');

    // 教程管理
    Route::resource('tutorials', 'TutorialController');

    // 章节管理
    Route::put('chapters/sort', 'ChapterController/sort');
    Route::resource('chapters', 'ChapterController');

    // 评论管理
    Route::put('comments/:id/audit', 'CommentController/audit');
    Route::get('comments', 'CommentController/index');
    Route::delete('comments/:id', 'CommentController/delete');

    // 用户管理
    Route::put('users/:id/status', 'UserController/status');
    Route::get('users', 'UserController/index');
    Route::get('users/:id', 'UserController/read');

    // 文件管理
    Route::get('upload', 'UploadController/index');
    Route::post('upload', 'UploadController/upload');
    Route::delete('upload/:id', 'UploadController/delete');

    // 系统配置
    Route::get('config', 'ConfigController/index');
    Route::put('config', 'ConfigController/update');
    Route::post('config', 'ConfigController/save');
    Route::delete('config/:id', 'ConfigController/delete');

    // 配置分组管理
    Route::put('configGroups/sort', 'ConfigController/groupSort');
    Route::get('configGroups/:id', 'ConfigController/groupRead');
    Route::get('configGroups', 'ConfigController/groupIndex');
    Route::post('configGroups', 'ConfigController/groupSave');
    Route::put('configGroups/:id', 'ConfigController/groupUpdate');
    Route::delete('configGroups/:id', 'ConfigController/groupDelete');

    // 消息管理
    Route::get('messages/unread-count', 'MessageController/unreadCount');
    Route::put('messages/read-all', 'MessageController/markAllRead');
    Route::put('messages/:id/read', 'MessageController/markRead');
    Route::get('messages', 'MessageController/index');
    Route::delete('messages/:id', 'MessageController/delete');

    // 重定向管理
    Route::resource('redirects', 'RedirectController');

    // 爬虫记录
    Route::get('crawlers/stats', 'CrawlerController/stats');
    Route::get('crawlers/bot-types', 'CrawlerController/botTypes');
    Route::delete('crawlers/cleanup', 'CrawlerController/cleanup');
    Route::get('crawlers', 'CrawlerController/index');

    // AI 渠道管理
    Route::post('ai-channels/:id/test', 'AiChannelController/test');
    Route::resource('ai-channels', 'AiChannelController');

    // AI 模板管理
    Route::resource('ai-templates', 'AiTemplateController');

    // AI 关键词管理
    Route::post('ai-keywords/batch-generate', 'AiKeywordController/batchGenerate');
    Route::post('ai-keywords/:id/generate', 'AiKeywordController/generate');
    Route::resource('ai-keywords', 'AiKeywordController');

    // AI 任务管理
    Route::get('ai-tasks/stats', 'AiTaskController/stats');
    Route::post('ai-tasks/consume', 'AiTaskController/consume');
    Route::post('ai-tasks/batch-publish', 'AiTaskController/batchPublish');
    Route::delete('ai-tasks/cleanup', 'AiTaskController/cleanup');
    Route::post('ai-tasks/:id/run', 'AiTaskController/run');
    Route::post('ai-tasks/:id/publish', 'AiTaskController/publish');
    Route::resource('ai-tasks', 'AiTaskController');

    // AI 日志
    Route::get('ai-logs/stats', 'AiLogController/stats');
    Route::get('ai-logs', 'AiLogController/index');
})->middleware(\app\admin\middleware\AdminAuth::class);
