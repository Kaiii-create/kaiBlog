<?php
use think\facade\Route;

// ============ 公开接口 ============

// 认证
Route::post('auth/register', 'Auth/register');
Route::post('auth/login', 'Auth/login');

// 验证码
Route::get('captcha', 'CaptchaController/index');

// 文章 (详情路由放前面)
Route::get('articles/:id', 'ArticleController/read');
Route::get('articles', 'ArticleController/index');

// 栏目
Route::get('categories/:id', 'CategoryController/read');
Route::get('categories', 'CategoryController/index');

// 教程
Route::get('tutorials/:id/chapters', 'TutorialController/chapters');
Route::get('tutorials/:id', 'TutorialController/read');
Route::get('tutorials', 'TutorialController/index');
Route::get('chapters/:id', 'TutorialController/chapterRead');

// 评论
Route::get('comments', 'CommentController/index');

// SEO
Route::get('sitemap.xml', 'SeoController/sitemap');
Route::get('robots.txt', 'SeoController/robots');
Route::get('seo/:type/:id', 'SeoController/info');
Route::get('seo/:type', 'SeoController/info');

// 配置
Route::get('config', 'ConfigController/index');

// ============ 用户接口（需要登录） ============
Route::group('user', function () {
    Route::get('profile', 'Auth/profile');
    Route::put('profile', 'Auth/updateProfile');
    Route::put('password', 'Auth/changePassword');
    Route::get('comments', 'Auth/myComments');
    Route::get('browse-history', 'Auth/browseHistory');

    Route::post('articles/:id/like', 'ArticleController/like');
    Route::post('articles/:id/favorite', 'ArticleController/favorite');
    Route::get('favorites', 'ArticleController/favorites');

    Route::post('comments', 'CommentController/save');
    Route::post('upload', 'UploadController/upload');

    // 消息通知
    Route::get('messages/unread-count', 'MessageController/unreadCount');
    Route::put('messages/read-all', 'MessageController/markAllRead');
    Route::put('messages/:id/read', 'MessageController/markRead');
    Route::get('messages', 'MessageController/index');
    Route::delete('messages/:id', 'MessageController/delete');
})->middleware(\app\api\middleware\ApiAuth::class);
