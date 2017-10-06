<?php

/**
 * Response
 * 接口返回的响应结构体
 * 
 * @author lee<93853507@qq.com> 2013-10-14 11:19
 */

namespace Core;

class Response {

    private $_code = 0;
    private $_result = NULL;
    private $_message = '';

    /**
     * 成功返回的code状态
     */
    const CODE_SUCCESS = 0;

    public function __construct($res = null, $code = self::CODE_SUCCESS) {
        $this->_code = (int) $code;

        if ($this->_code === self::CODE_SUCCESS) {
            $this->_result = $res;
        } else {
            $this->_message = $res;
        }
    }

    public function code() {
        return $this->_code;
    }

    public function result() {
        return $this->_result;
    }

    public function isSuccess() {
        return $this->code() === self::CODE_SUCCESS;
    }

    public function message() {
        return $this->_message;
    }

    public function getResponse() {
        $data = array(
            'code' => $this->_code
        );
        if ($this->isSuccess() && $this->_result) {
            $data['result'] = $this->_result;
        } elseif (config()['debug'] && $this->_message) {
            $data['message'] = $this->_message;
        }
        return $data;
    }

    public function response() {
        return json_encode($this->getResponse());
    }

    public function __toString() {
        return $this->response();
    }

}
