<?php

namespace App\Model;

class Model extends \Core\Model {
    use \ServiceSingleton;

    protected static function _serviceInit($key) {
        $class = get_called_class();
        return new $class($key);
    }
    
    protected $_errCode = 0;
    protected $_errMsg = "";
    
    public function errCode() {
        return $this->_errCode;
    }

    public function errMsg() {
        return $this->_errMsg;
    }

    public function setError($errCode, $errMsg = "") {
        $this->_errCode = $errCode;
        $this->_errMsg = $errMsg;
    }

//    function table() {
//        return $this->name();
//    }

    function cache($key = "default") {
        return \Core\Cache::getInstance($key);
    }

    function redis($key = "default") {
        return \Core\Redis::getInstance($key);
    }

//    function db($key = "default") {
//        return \Core\PDO::getInstance($key);
//    }
//    
//    function shardDb($sid = 0) {
//        if(!$sid){
//            throw new \Core\Exception("拆分的数据库必须传入sid", 9999);
//        }
//        return \Core\PDO::getInstance("shard_{$sid}");
//    }
}
