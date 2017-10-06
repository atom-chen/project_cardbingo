<?php

namespace Core;

class Cache {
    use \ServiceSingleton;
    private $_data = [];

    public function get($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : FALSE;
    }

    public function set($key, $value) {
        $this->_data[$key] = $value;
    }

    public function del($key) {
        unset($this->_data[$key]);
    }

}
