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
```
