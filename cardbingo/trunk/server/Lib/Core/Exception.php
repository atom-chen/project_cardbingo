<?php

/**
 * 异常类
 * 
 * @author lee<93853507@qq.com> 2013-10-14 11:19
 */

namespace Core;

class Exception extends \Exception {

//    private static $_codeMessage = array(
//        101 => "系统参数出错",
//        102 => "module不存在",
//        103 => "method不存在",
//        104 => "control返回的结果必须是clsResponse的实例",
//        201 => "配置文件类型不存在",
//        202 => "数据库连接失败",
//        203 => "数据库配置不存在",
//        204 => "MC配置不存在",
//        205 => "实例化的模型不存在",
//        301 => "MC连接失败",
//        302 => "无法得到正常的活跃数据库ID，Cron出错"
//    );

    public function __toString() {
        return "[Code: {$this->getCode()}] {$this->getMessage()} [{$this->getFile()}] ({$this->getLine()}) \r\n" ;
    }

    public static function out($e) {
        writeLog($e, get_class($e));
    }

}