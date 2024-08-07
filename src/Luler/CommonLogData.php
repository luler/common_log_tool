<?php

namespace Luler;

class CommonLogData
{
    private $level = CommonLogLevel::INFO;
    private $code = 0;
    private $url = 'log';
    private $waste_time = 0;
    private $message = '';
    private $other = '';
    private $other_id = '';
    private $create_time = 0;
    private $client_ip = '0.0.0.0';
    private $server_ip = '0.0.0.0';

    public function __construct()
    {
        $this->setCreateTime(intval(microtime(true) * 1000));
    }

    /**
     * 创建实例
     * @return CommonLogData
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function create(): CommonLogData
    {
        return new self();
    }

    /**
     * 获取日志数据
     * @return array
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getData(): array
    {
        return [
            'level' => $this->level,
            'code' => $this->code,
            'url' => $this->url,
            'waste_time' => $this->waste_time,
            'message' => $this->message,
            'other' => $this->other,
            'other_id' => $this->other_id, //给某一批日志设置相同的ID，有助于链式跟踪
            'create_time' => $this->create_time,
            'client_ip' => $this->client_ip,
            'server_ip' => $this->server_ip,
        ];
    }

    public function setLevelInfo()
    {
        $this->level = CommonLogLevel::INFO;
        return $this;
    }

    public function setLevelWarning()
    {
        $this->level = CommonLogLevel::WARNING;
        return $this;
    }

    public function setLevelError()
    {
        $this->level = CommonLogLevel::ERROR;
        return $this;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
        return $this;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function setWasteTime(float $waste_time)
    {
        $this->waste_time = $waste_time;
        return $this;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function setOther(string $other)
    {
        $this->other = $other;
        return $this;
    }

    public function setOtherId(string $other_id)
    {
        $this->other_id = $other_id;
        return $this;
    }

    public function setCreateTime(int $create_time = 0)
    {
        $this->create_time = $create_time;
        return $this;
    }

    public function setClientIp(string $client_ip = '0.0.0.0')
    {
        $this->client_ip = $client_ip;
        return $this;
    }

    public function setServerIp(string $server_ip = '0.0.0.0')
    {
        $this->server_ip = $server_ip;
        return $this;
    }
}