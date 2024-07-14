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
    private $enable_other_id = false; //是否启用其他信息ID功能
    private $other_id = ''; //每个实例化唯一ID，可用于链式跟踪
    private $log_data = []; //日志数据

    public function __construct(string $appid, string $appsecret, string $host, string $project_name = 'common_log', bool $enable_other_id = false)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->host = $host;
        $this->project_name = $project_name;
        $this->enable_other_id = $enable_other_id;
        if ($this->enable_other_id) {
            $this->refreshOtherId();
        }
    }

    /**
     * 刷新其他信息ID
     * @return void
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function refreshOtherId()
    {
        $this->enable_other_id && $this->other_id = session_create_id();
    }

    /**
     * 使用redis作为缓存驱动
     * @param \Redis $redis
     * @return $this
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function useRedis(\Redis $redis)
    {
        $this->redis = $redis;
        return $this;
    }

    /**
     * 获取访问凭证
     * @return mixed|string
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    private function getLogAccessToken()
    {
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
    }

    /**
     * 推送日志到通用日志系统
     * @return bool
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function saveLog(): bool
    {
        try {
            foreach ($this->log_data as $key => $value) {
                $value = $value->getData();
                $value['other'] = !empty($this->other_id) ? ('#' . $this->other_id . '# ' . $value['other']) : $value['other'];
                $value['project_name'] = $this->project_name;
                $this->log_data[$key] = $value;
            }
            $data = [];
            $data['authorization'] = $this->getLogAccessToken();
            $data['data'] = $this->log_data;
            $this->log_data = [];
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
     * 添加日志数据
     * @param CommonLogData $logData
     * @return $this
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function addCommonLogData(CommonLogData $logData)
    {
        $this->log_data[] = $logData;
        return $this;
    }

    /**
     * @return bool
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function errorLog()
    {
        foreach ($this->log_data as $value) {
            $value->setLevel(CommonLogLevel::ERROR);
        }
        return $this->saveLog();
    }

    public function warningLog()
    {
        foreach ($this->log_data as $value) {
            $value->setLevel(CommonLogLevel::WARNING);
        }
        return $this->saveLog();
    }

    public function infoLog()
    {
        foreach ($this->log_data as $value) {
            $value->setLevel(CommonLogLevel::INFO);
        }
        return $this->saveLog();
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
