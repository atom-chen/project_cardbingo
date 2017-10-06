<?php

/**
 * 调度、路由分发
 * 
 * @author lee<93853507@qq.com> 2014-10-20 12:12
 */

namespace Core;

class Dispatch {

    private $_params = [];

    public function __construct($params = []) {
        $this->_params = $params ? $params : $_REQUEST;
    }

    public function run() {
        try {
            list($nameController, $nameAction, $params) = $this->router();
            //dump($nameController, $nameAction, $params);
            if (!class_exists($nameController)) {
                throw new Exception("Controller not found", 101);
            }
            $clsController = new $nameController($nameController, $nameAction, $params);
            $clsController->beforeAction();
            $response = $clsController->$nameAction();
            $clsController->afterAction($response);
        } catch (Exception $e) {
            //TODO 需要考虑根据不同的controll模型， 输出不同的错误展示方式
            $errmsg = config()['debug'] ? $e->getMessage() : "Server Error";
            echo new \Core\Response($errmsg, $e->getCode());
        }
    }

    protected function router() {
        //TODO支持其他路由规则，暂时只实现最简单的规则
        list($nameController, $nameAction) = explode(".", $this->_params['method']);
        if(!$nameController){
            $nameController = "Index";
        }
        
        $nameController = "App\\Controller\\".  ucfirst($nameController);
        $nameAction = $nameAction ? lcfirst($nameAction) : "index";
        return array($nameController, $nameAction, $this->_params);
    }

}
