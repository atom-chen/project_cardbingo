<?php

/**
 * Model基类
 * 
 * @author lee<93853507@qq.com> 2013-9-2 15:19
 */

namespace Core;

abstract class Model {

    /**
     * model标记
     * @var string
     */
    protected $_name = "name";

    public function name() {
        return $this->_name;
    }

    public function __toString() {
        return ucfirst($this->name()) . "Model";
    }
}