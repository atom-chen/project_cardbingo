<?php
namespace Core;

class HttpCurl {
    private $_method = "GET";
    private $_user_agent = NULL;
    private $_cookie = NULL;
    private $_data = NULL;
    private $_connect_timeout = NULL;
    private $_timeout = NULL;
    private $_nobody = false;
    private $_hasheader = false;
    private $_url = '';
    private $_result = NULL;
    private $_errno = 0;
    private $_error = NULL;
    private $_refer = NULL;
    private $_info = NULL;
    public function __construct() {
    }
    public function info() {
        return $this->_info;
    }
    public function reset() {
        $this->_errno = 0;
        $this->_error = NULL;
        $this->_method = 'GET';
        $this->_user_agent = NULL;
        $this->_cookie = NULL;
        $this->_connect_timeout = NULL;
        $this->_timeout = NULL;
        $this->_info = NULL;
    }
    public function post($url, $data = NULL, $connect_timeout = NULL, $timeout = NULL, $user_agent = NULL) {
        return $this->request ( 'POST', $url, $data, $connect_timeout, $timeout, $user_agent );
    }
    public function get($url, $data = NULL, $connect_timeout = NULL, $timeout = NULL, $user_agent = NULL) {
        return $this->request ( 'GET', $url, $data, $connect_timeout, $timeout, $user_agent );
    }
    public function request($method, $url, $data = NULL, $connect_timeout = NULL, $timeout = NULL, $user_agent = NULL) {
        $this->setMethod ( $method );
        $this->setUrl ( $url );
        $this->setData ( $data );
        $this->setTimeout ( $timeout );
        $this->setConnectTimeout ( $connect_timeout );
        $this->setUserAgent ( $user_agent );
        $this->setCookie ( NULL );
        $this->setNoBody ( false );
        $this->setHasHeader ( false );
        $res = $this->send ();
        if ($res == 0) {
            return $this->result ();
        } else {
            return $this->_halt ();
        }
    }
    
    /**
     * Enter description here...
     */
    private function _halt() {
        $err = sprintf ( "[%s]\t%s\n", date ( 'H:i:s' ), $_SERVER ['REQUEST_URI'] );
        $err .= sprintf ( "DATA:%s\n", http_build_query ( $this->_data ) );
        $err .= sprintf ( "\tErrno : %s\n\tError:%s\n\tResult : %s\n", $this->_errno, $this->_error, $this->_result );
        writeLog ( $err, "http.err" );
        return FALSE;
    }
    private function _build_query($data = NULL) {
        if (! is_array ( $data )) {
            return $data;
        }
        $data = $this->_make_params ( $data );
        return implode ( '&', $data );
    }
    private function _make_params($data, $pre = '', $params = array()) {
        foreach ( $data as $k => $v ) {
            $vp = $pre ? $pre . "[{$k}]" : $k;
            if (is_array ( $v )) {
                $params = $this->_make_params ( $v, $vp, $params );
            } else {
                $v = urlencode ( $v );
                $params [] = "{$vp}=" . $v;
            }
        }
        return $params;
    }
    public function send() {
        $data = $this->_build_query ( $this->_data );
        $res = $this->_request_curl ( $this->_method, $this->_url, $data, $this->_user_agent, $this->_timeout, $this->_connect_timeout, $this->_cookie, $this->_nobody, $this->_hasheader, $this->_refer );
        return $res;
    }
    public function setData($data) {
        $this->_data = $data;
    }
    public function result() {
        return $this->_result;
    }
    public function setUrl($url = NULL) {
        $this->_url = $url;
    }
    public function setMethod($method = NULL) {
        $this->_method = $method;
    }
    public function setCookie($cookie = NULL) {
        $this->_cookie = $cookie;
    }
    public function setUserAgent($user_agent = NULL) {
        $this->_user_agent = $user_agent;
    }
    public function setHasHeader($hasheader = false) {
        $this->_hasheader = $hasheader;
    }
    public function setNoBody($nobody = false) {
        $this->_nobody = $nobody;
    }
    public function setConnectTimeout($timeout = NULL) {
        $this->_connect_timeout = $timeout;
    }
    public function setTimeout($timeout = NULL) {
        $this->_timeout = $timeout;
    }
    public function addParam($key, $value) {
        $this->_data [$key] = $value;
    }
    private function _request_curl($method, $url, $data = NULL, $user_agent = NULL, $timeout = NULL, $connect_timeout = NULL, $cookie = NULL, $nobody = NULL, $hasheader = false) {
        if (! function_exists ( 'curl_init' )) {
            $this->_errno = 900;
            $this->_error = "Function(curl) not exists!";
            return $this->_errno;
        }
        $headers = array (
                'Accept-Language: zh-cn',
                'Connection: Keep-Alive',
                'Cache-Control: no-cache' 
        );
        $options = array (
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_USERAGENT => $user_agent 
        );
        if ($method == 'POST') {
            $options [CURLOPT_POST] = 1;
            if ($data) {
                $options [CURLOPT_POSTFIELDS] = $data;
            }
        } else {
            $options [CURLOPT_POST] = 0;
            if ($data) {
                $url .= (strpos ( $url, "?" ) > 0 ? "&" : "?") . $data;
            }
        }
        $options [CURLOPT_URL] = $url;
        if ($nobody !== NULL) {
            $options [CURLOPT_NOBODY] = $nobody && true;
        }
        if ($hasheader !== NULL) {
            $options [CURLOPT_HEADER] = $hasheader && true;
        }
        if ($timeout > 0) {
            $options [CURLOPT_TIMEOUT] = $timeout;
        }
        if ($connect_timeout > 0) {
            $options [CURLOPT_CONNECTTIMEOUT] = $connect_timeout;
        }
        
        $ch = curl_init ();
        curl_setopt_array ( $ch, $options );
        $this->_result = curl_exec ( $ch );
        $this->_errno = curl_errno ( $ch );
        $this->_error = curl_error ( $ch );
        $this->_info = curl_getinfo ( $ch );
        // print_r($this->_info);
        // exit;
        curl_close ( $ch );
        return $this->_errno;
    }
}
