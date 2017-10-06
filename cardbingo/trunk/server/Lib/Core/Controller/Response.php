<?php

/**
 * 针对Json或者其他需要系列化Response的控制器抽象类
 * 
 * @author lee<93853507@qq.com> 2013-10-14 11:19
 */

namespace Core\Controller;

abstract class Response extends \Core\Controller {
    protected $_charset = 'utf-8';
    protected $_contentType = '';
    public function beforeAction() {
        parent::beforeAction();
    }

    public function afterAction($response) {
        parent::afterAction($response);
        
        if ($response instanceof \Core\Response) {
            echo $response;
            //$this->render($response, $this->_charset, $this->_contentType);
        }else {
            throw new \Core\Exception("【{$this->c}.{$this->a}】:\n controller结构返回异常， 需要返回\core\Response类型 \n Params: \n" . var_export($this->params, true), 104);
        }
    }
}
