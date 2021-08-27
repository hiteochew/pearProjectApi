<?php

namespace think;
// 加载基础文件
require __DIR__ . '/thinkphp/base.php';

// 全局设置跨域
/* $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
$allow_origin = array(
    '*' //这里可以录入域名列表
);

if(in_array($origin, $allow_origin)){
    header('Access-Control-Allow-Origin:'.$origin);
    header('Access-Control-Allow-Methods:POST');
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
}  */

// think文件检查，防止TP目录计算异常
file_exists('think') || touch('think');

// 执行应用并响应
Container::get('app', [__DIR__ . '/application/'])->run()->send();
