<?php

/**
 * 针对需要用到模版显示的的控制器抽象类
 * 
 * @author lee<93853507@qq.com> 2013-10-14 11:19
 */

namespace Core\Controller;

abstract class View extends \Core\Controller {

    public $_autoRender = true;
    public $_autoLayout = true;

    /**
     * 模版参数
     * @var array 
     */
    public $_tplParams = [];

    public function beforeAction() {
        parent::beforeAction();
    }

    public function afterAction($response) {
        parent::afterAction($response);

        if ($this->_autoRender) {
            $this->template();
        }
    }

    protected function _parsePath($control, $action) {
        $start = strrpos($control, "\\");
        if ($start === FALSE) {
            $start = 0;
        }
        $controll = substr($control, $start + 1);

        return $controll . DIRECTORY_SEPARATOR . $action;
    }

    public function assign($name, $value = '') {
        if (is_array($name)) {
            $this->_tplParams = array_merge($this->_tplParams, $name);
            return $this;
        } else {
            $this->_tplParams [$name] = $value;
        }
        return $this;
    }

    protected function template($path = "", $tplParams = []) {
        $content = '';
        if ($this->_autoLayout) {
            $content .= $this->fetch('header');
        }
        $content .= $this->fetch($path);
        if ($this->_autoLayout) {
            $content .= $this->fetch('footer');
        }

        $this->render($content);
        $this->_autoRender = false;
    }

    public function displays($path = "", $tplParams = []) {
        $content .= $this->fetch($path);
        echo $content;
    }

    /**
     * 获取的模版相对路径
     * @param type $path
     * @return type
     * @throws Exception
     */
    public function fetch($path = '', $tplParams = []) {
        if (!$path) {
            $path = $this->_parsePath($this->c, $this->a);
        }
        $templateFile = DIR_VIEW . "/{$path}.html";
        if (!is_file($templateFile)) {
            throw new \Core\Exception("tmplate[{$templateFile}] not exist", 888);
        }

        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        extract($this->_tplParams, EXTR_OVERWRITE);
        extract($tplParams, EXTR_OVERWRITE);
        include $templateFile;

        // 获取并清空缓存
        $content = ob_get_clean();
        return $content;
    }

}
