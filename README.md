# luler/common_log_tool

通用日志系统工具，封装日志推送api（php版本）

# 助手类列表如下

- CommonLogTool

# 使用示例

```php

<?php

require 'vendor/autoload.php';

$log = new \Luler\CommonLogTool(
    'admin', //账号
    '******', //密码
    'http://ip:8888', //通用系统地址
    'common_log', //项目代码
    true
);

$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->infoLog();
var_dump($res);//bool,true-推送成功，false-推送失败
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->warningLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->errorLog();
var_dump($res);
$log->refreshOtherId(); //刷新日志ID
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->infoLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->warningLog();
var_dump($res);
$res = $log->addCommonLogData(\Luler\CommonLogData::create()->setMessage('你是'))->errorLog();
var_dump($res);
```
