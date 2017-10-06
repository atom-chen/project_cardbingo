<?php
/**
 * 控制器基类
 */
namespace App\Controller;

abstract class Controller extends \Core\Controller\Response {
    public function __construct($controller, $action, $params) {
        parent::__construct($controller, $action, $params);
    }
}
