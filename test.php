<?php

require 'vendor/autoload.php';

$log = new \Luler\CommonLogTool(
    'admin', //账号
    '******', //密码
    'http://ip:8888', //通用系统地址
    'common_log', //项目代码
    true
);

$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelInfo()->setMessage('你是'))->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelWarning()->setMessage('你是'))->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelError()->setMessage('你是'))->saveLog();
var_dump($res);
$log->refreshOtherId();
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelInfo()->setMessage('你是'))->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelWarning()->setMessage('你是'))->saveLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setLevelError()->setMessage('你是'))->saveLog();
var_dump($res);


