<?php

namespace Luler;

class CommonLogTool
{
    private $appid = ''; //账号
    private $appsecret = ''; //密码
    private $host = ''; //系统地址，格式:http://127.0.0.1
    private $url_getAccessToken = '/api/getAccessToken'; //获取登录凭证接口
    private $url_saveLog = '/api/saveLog'; //保存日志接口
    private $project_name = ''; //项目代码
    private $redis = null; //redis缓存驱动

    public function __construct(string $appid, string $appsecret, string $host, $project_name = 'common_log')
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->host = $host;
        $this->project_name = $project_name;
    }

    public function useRedis(\Redis $redis)
    {
        $this->redis = $redis;
        return $this;
    }

    private function getLogAccessToken()
    {
        try {
            $res = $this->getCacheDataByKey();
            if (!empty($res)) {
                return $res;
            }

            $url = $this->host . $this->url_getAccessToken;
            $post_data = [
                'appid' => $this->appid,
                'appsecret' => $this->appsecret,
            ];
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            $res = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($res, true) ?: [];

            if (!isset($res['code']) || $res['code'] != 200) {
                throw new \Exception(($res['message'] ?? '请求异常，可能网络不通或系统繁忙'));
            }

            $this->setCacheData([
                'expires_in' => $res['info']['expires_in'] - 60,
                'expire_time' => time() + $res['info']['expires_in'] - 60,
                'access_token' => $res['info']['access_token'],
            ]);
            return $res['info']['access_token'];
        } catch (\Throwable $e) {
            throw new \Exception('通用日志组件报错：' . $e->getMessage());
        }
    }

    /**
     * 推送日志到通用日志系统
     * @param array $log_data
     * @return bool
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    private function saveLog(array $log_data): bool
    {
        try {
            foreach ($log_data as &$value) {
                $value = [
                    'project_name' => $this->project_name, //必填
                    'level' => $value['level'] ?? '', //必填
                    'code' => $value['code'] ?? 0, //非必填
                    'url' => $value['url'] ?? '', //必填
                    'waste_time' => $value['waste_time'] ?? 0, //非必填
                    'message' => $value['message'] ?? '', //必填
                    'other' => $value['other'] ?? '', //非必填
                    'create_time' => $value['create_time'] ?? date('Y-m-d H:i:s'), //必填
                    'client_ip' => $value['client_ip'] ?? '127.0.0.1', //必填
                    'server_ip' => $value['server_ip'] ?? '127.0.0.1', //必填
                ];
            }
            $data = [];
            $data['authorization'] = $this->getLogAccessToken();
            $data['data'] = $log_data;
            $url = $this->host . $this->url_saveLog;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);//限时间，不能长期阻塞
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['content-type: application/json']);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, 256));
            $res = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($res, true) ?: [];
            if (!isset($res['code']) || $res['code'] != 200) {
                throw new \Exception($res['message'] ?? '请求异常，可能网络不通或系统繁忙');
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception('通用日志组件报错：' . $e->getMessage());
        }

    }

    /**
     * @param array $log_data //格式：
     * @return bool
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function errorLog(array $log_data)
    {
        foreach ($log_data as &$value) {
            $value['level'] = 'error';
        }
        return $this->saveLog($log_data);
    }

    public function warningLog(array $log_data)
    {
        foreach ($log_data as &$value) {
            $value['level'] = 'warning';
        }
        return $this->saveLog($log_data);
    }

    public function infoLog(array $log_data)
    {
        foreach ($log_data as &$value) {
            $value['level'] = 'info';
        }
        return $this->saveLog($log_data);
    }

    private function getCacheFilePath()
    {
        return dirname(__FILE__) . '/../../token.cache';
    }

    private function getCacheKey()
    {
        return 'CommonLogTool:' . md5(join(',', [$this->appid, $this->appsecret, $this->host]));
    }

    private function getAllCacheData(): array
    {
        if (!file_exists($this->getCacheFilePath())) {
            file_put_contents($this->getCacheFilePath(), json_encode([]));
            chmod($this->getCacheFilePath(), 0777);
        }
        $data = file_get_contents($this->getCacheFilePath());
        $data = json_decode($data, true) ?: [];
        $current_time = time();
        foreach ($data as $key => $value) {
            if ($current_time >= $value['expire_time']) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    private function getCacheDataByKey()
    {
        if (!is_null($this->redis)) {
            return $this->redis->get($this->getCacheKey());
        } else {
            return $this->getAllCacheData()[$this->getCacheKey()]['access_token'] ?? '';
        }
    }

    private function setCacheData(array $value): void
    {
        if (!is_null($this->redis)) {
            $this->redis->set($this->getCacheKey(), $value['access_token'], $value['expires_in']);
        } else {
            $data = $this->getAllCacheData();
            $data[$this->getCacheKey()] = $value;
            $data = json_encode($data, 256);
            file_put_contents($this->getCacheFilePath(), $data);
        }
    }


}