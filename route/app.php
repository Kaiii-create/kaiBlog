<?php
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

// Diagnostic route
Route::get('diag', function () {
    $app = app();
    return json([
        'base_path' => $app->getBasePath(),
        'app_path' => $app->getAppPath(),
        'runtime_path' => $app->getRuntimePath(),
        'root_path' => $app->getRootPath(),
        'request_path' => request()->pathinfo(),
        'request_url' => request()->url(),
        'app_name' => $app->http->getName(),
        'http_path' => $app->http->getPath(),
        'api_dir' => is_dir($app->getBasePath() . 'api'),
        'admin_dir' => is_dir($app->getBasePath() . 'admin'),
        'api_route' => file_exists($app->getBasePath() . 'api/route/app.php'),
    ]);
});
