<?php
return [
    'default' => [
        'type'       => 'local',
        'root'       => app()->getRootPath() . 'public/uploads',
        'url'        => '/uploads',
        'visibility' => 'public',
    ],
];
