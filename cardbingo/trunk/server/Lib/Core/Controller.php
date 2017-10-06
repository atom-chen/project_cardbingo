<?php

/**
 * Controller基类
 * 
 * @author lee<93853507@qq.com> 2013-9-2 15:19
 */

namespace Core;

abstract class Controller {

    /**
     * 设置当前执行的请求controller
     * @var string
     */
    public $c = "default";

    /**
     * 设置当前执行的请求动作action
     * @var string
     */
    public $a = "index";

    /**
     * 请求参数数组
     * @var array
     */
    public $params = array();
    public static $_controlCallbacks = [];
    
    public function __construct($controller, $action, $params) {
        $this->c = $controller;
        $this->a = $action;
        $this->params = $params;
    }

    public function __call($name, $arguments) {
        throw new Exception("Controller:{$name} action  not found,params :" . json_encode($arguments), 102);
    }

    /**
     * controller初始化之后调用
     * 
     * @example __construct 
     */
    public function beforeAction() {
//        dump("beforeAction");
//        dump($this->params);
    }

    /**
     * controller结束之后马上调用,注意与__destruct的执行顺序是不一样的
     * 如果涉及到多个action， 不建议使用， 可以用Dispatch::callBack替代
     * 
     * @example  引入view层
     */
    public function afterAction($response) {
        /**
         * @example 
         * $view = new \core\View($this->c.DS.$this->a)
         * $view->assign((array)$this)
         */
//        dump("afterAction");
        self::runAfterAction();
        return $response; 
        //fastcgi_finish_request();
        //wlog("response");
        //self::runAfterAction();
        
    }
    
    protected function render($content, $charset = 'utf-8', $contentType = 'text/html')
    {
        // 网页字符编码
        header('Content-Type:' . $contentType . '; charset=' . $charset);
        //header('Cache-control: ' . C('HTTP_CACHE_CONTROL')); // 页面缓存控制
        // 输出模板文件
        echo $content;
    }
    
    protected function redirect($method = "Index.index") {
        
    }

    public static function setAfterAction($callback, $params = [], $index = "") {
        if ($index) {
            if (!isset(self::$_controlCallbacks[$index])) {
                self::$_controlCallbacks[$index] = [
                    'callback' => $callback,
                    'params' => $params
                ];
            }
        } else {
            self::$_controlCallbacks[] = [
                'callback' => $callback,
                'params' => $params
            ];
        }
        
    }

    public static function runAfterAction() {
//        wlog("nihaoa");
        if (self::$_controlCallbacks) {
            foreach (self::$_controlCallbacks as $k => $controlCallback) {
                call_user_func_array($controlCallback['callback'], $controlCallback['params']);
            }
            self::$_controlCallbacks = [];
        }
    }
}
