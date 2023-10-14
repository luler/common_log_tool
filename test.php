<?php

require 'vendor/autoload.php';

$log = new \Luler\CommonLogTool(
    'admin', //账号
    '******', //密码
    'http://192.168.1.101:8888', //通用系统地址
    'common_log' //项目代码
);

$res = $log->infoLog([
    [
        'code' => 200, //非必填
        'url' => '/api/test', //必填
        'waste_time' => 1.25, //非必填
        'message' => '请求成功', //必填
        'other' => '暂无', //非必填
        'create_time' => intval(microtime(true) * 1000), //必填
        'client_ip' => '127.0.0.1', //必填
        'server_ip' => '127.0.0.1', //必填
    ]
]);

var_dump($res);

