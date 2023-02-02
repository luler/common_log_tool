# luler/common_log_tool

通用日志系统工具，封装日志推送api（php版本）

# 助手类列表如下

- CommonLogTool

# 使用示例

```php

$log = new \Luler\CommonLogTool(
    'xxx', //账号
    'xxx', //密码
    'http://127.0.0.1:8888', //通用系统地址
    'common_log' //项目代码

$res = $log->infoLog([
    [
        'code' => 200, //非必填
        'url' => '/api/test', //必填
        'waste_time' => 1.25, //非必填
        'message' => '请求成功', //必填
        'other' => '暂无', //非必填
        'create_time' => date('Y-m-d H:i:s'), //必填
        'client_ip' => '127.0.0.1', //必填
        'server_ip' => '127.0.0.1', //必填
    ]
]);

var_dump($res); //bool,true-推送成功，false-推送失败
```
