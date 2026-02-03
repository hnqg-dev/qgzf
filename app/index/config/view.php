<?php
return [

    'view_path' => app()->getRootPath() . 'public/template/' . (get_sys_config()['mbname'] ?? ''),
    'view_dir_name' => false,

    'tpl_replace_string' => [
        '__PUBLIC__' => '/public',
        '__STATIC__' => '/template',
        '__IMG__'    => '/template/' . (get_sys_config()['mbname'] ?? '') . 'img',
        '__JS__'     => '/template/' . (get_sys_config()['mbname'] ?? '') . 'js',
        '__CSS__'    => '/template/' . (get_sys_config()['mbname'] ?? '') . 'css',
        '__ICONF__'  => '/template/' . (get_sys_config()['mbname'] ?? '') . 'font',
        '__SFACE__'  => '/upload/face/sysimg',
        '__UFACE__'  => '/upload/face',
        '__WEBTIT__' => get_sys_config()['webtitle'] ?? '',
        '__MBNAME__' => get_sys_config()['mbname'] ?? '',
        '__REGSTATE__' => get_sys_config()['regstate'] ?? '',
        '__VER__' => get_sys_config()['version'] ?? '',
        '__COPYRIGHT__' => get_sys_config()['copyright'] ?? '',
        '__YEAR__' => get_sys_config()['year'] ?? '',


    ],
];
