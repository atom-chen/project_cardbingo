<?php

namespace Core;

class Autoloader {

    private $_namespaceConfig = [
        __NAMESPACE__ => __DIR__
    ];

    public function __construct($namespaceConfig = []) {
        if (!empty($namespaceConfig)) {
            $this->_namespaceConfig = array_merge($this->_namespaceConfig, $namespaceConfig);
        }
    }

    public static function register($namespaceConfig = [],$prepend = false) {
        spl_autoload_register(array(new self($namespaceConfig), 'autoload'), true, $prepend);
        //dump(spl_autoload_functions());
    }

    public function autoload($className) {
        //dump(__LINE__.$className);
        $parts = explode('\\', $className);
        //dump(__LINE__.$parts);
        if ($parts) {
            $ns = array_shift($parts);
            if (isset($this->_namespaceConfig[$ns])) {
                $filepath = $this->_namespaceConfig[$ns] .
                        implode(DIRECTORY_SEPARATOR, $parts) . '.php';
                //dump(__LINE__.$filepath);
                is_file($filepath) && require $filepath;
            }
        }
    }

}
