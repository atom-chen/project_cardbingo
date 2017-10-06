<?php

namespace Core;

class CacheRedis extends Cache {

    use \ServiceSingleton;

    private $_redisKey = "default";

    public function __construct($redisKey = "default") {
        if ($redisKey) {
            $this->_redisKey = $redisKey;
        }
    }

    public function get($key) {
        $data = parent::get($key);
        if ($data !== FALSE) {
            //内存中有值
            return $data;
        }
        $data = Redis::getInstance($this->_redisKey)->get($key);
        if ($data) {
            parent::set($key, $data);
            return $data;
        }
        return FALSE;
    }

    public function set($key, $value, $expire = 0) {
        parent::set($key, $value, $expire);
        if ($expire) {
            Redis::getInstance($this->_redisKey)->setEx($key, $expire, $value);
        } else {
            Redis::getInstance($this->_redisKey)->set($key, $value);
        }
    }

    public function del($key) {
        Redis::getInstance($this->_redisKey)->delete($key);
        unset($this->_cacheData[$key]);
    }

    public function getArray($key) {
        $data = parent::get($key);
        if ($data !== FALSE) {
            //内存中有值
            return $data;
        }

        $dataStr = Redis::getInstance($this->_redisKey)->get($key);
        $data = json_decode($dataStr, true);
        if (is_array($data)) {
            parent::set($key, $data);
            return $data;
        }
        return FALSE;
    }

    public function setArray($key, $value, $expire = 0) {
        if (!is_array($value) || !$value) {
            $value = [];
        }
        parent::set($key, $value, $expire);
        $valueStr = json_encode($value);
        if ($expire) {
            Redis::getInstance($this->_redisKey)->setEx($key, $expire, $valueStr);
        } else {
            Redis::getInstance($this->_redisKey)->set($key, $valueStr);
        }
    }
    
    public function llen($key) {
        return Redis::getInstance($this->_redisKey)->lLen($key);
    }
    
    public function lpushArray($key, $value) {
        if (!is_array($value) || !$value) {
            $value = [];
        }
        
        $valueStr = json_encode($value);
        
        Redis::getInstance($this->_redisKey)->lpush($key, $valueStr);

    }
    
    public function lpopArray($key) {
        $dataStr = Redis::getInstance($this->_redisKey)->lpop($key);
        $data = json_decode($dataStr, true);
        if (is_array($data)) {
            return $data;
        }
        return FALSE;

    }

}
