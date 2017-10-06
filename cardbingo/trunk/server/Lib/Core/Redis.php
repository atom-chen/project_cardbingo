<?php

namespace Core;

class Redis extends \Redis {

    private $_config = array(
        'host' => "127.0.0.1",
        'port' => 6379,
        'password' => "wacao#$@$$#!%#$@sdfrewt1234543^%",
        'database' => 11
    );
    use \ServiceSingleton;
    /**
     *  用于数据库 Redis 初始化
     * @param type $key
     * @return \Core\Redis
     */
    public static function _serviceInit($key) {
        $redis = new \Core\Redis($key);
        $redis->connect();
        return $redis;
    }

    public function __construct($config) {
        parent::__construct();
        if ($config && is_array($config)) {
            $this->_config = $config;
        } elseif ($config && is_string($config)) {
            $this->_config = config('Redis')[$config];
        }
    }

    public function connect() {
        try {
            if (!parent::connect($this->_config['host'], $this->_config['port'])) {
                throw new \Core\Exception("Redis连接失败 config:\n" . var_export($this->_config, true), 501);
            }
            parent::auth($this->_config['password']);
            parent::select($this->_config['database']);
//            $this->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); //使用IGBINARY格式化
//            $redis->setOption(Redis::OPT_PREFIX, 'myAppName:');
        } catch (Exception $e) {
            throw new \Core\Exception("Redis连接失败 config:\n" . var_export($this->_config, true), 502);
        }
        return true;
    }

}
