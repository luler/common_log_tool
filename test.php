<?php

require 'vendor/autoload.php';

$log = new \Luler\CommonLogTool(
    'admin', //账号
    '******', //密码
    'http://ip:8888', //通用系统地址
    'common_log', //项目代码
    true
);

$other_id = session_create_id(); //给某一批日志设置相同的ID，有助于链式跟踪
$res = $log->addCommonLogData(\Luler\CommonLogData::create()
    ->setLevelInfo() //默认是info
    ->setOtherId($other_id)
    ->setMessage('你是') //必须设置
)->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()
    ->setLevelWarning()
    ->setOtherId($other_id)
    ->setMessage('你是')
)->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()
    ->setLevelError()
    ->setOtherId($other_id)
    ->setMessage('你是')
)->saveLog();
var_dump($res);


