<?php

namespace Core;

class Log {
    use \ServiceSingleton;
    private $_logFile = 'normal';
    private $_msg = array();
    private $_scriptName = "";

    public function __construct($file = "") {
        if (!empty($file)) {
            $this->_logFile = $file;
        }
        $this->_scriptName = $_SERVER ['REQUEST_URI'] ? $_SERVER ['REQUEST_URI'] : ($_SERVER ['PHP_SELF'] ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME']);
    }

    public function setLogFile($file) {
        $this->_logFile = $file;
    }


    //记录日志
    public function writeLog($msg, $file = '') {
        //屏蔽记录日志功能
        $forbidLogs = config()["forbid_log"];
        if ($forbidLogs && isset($forbidLogs[$file]) && $forbidLogs[$file]) {
            return true;
        }

        if (!empty($file)) {
            $this->_logFile = $file;
        }

        $requestParam = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestParamStr = json_encode($requestParam);
        $msg = date('Y-m-d H:i:s') . "\t{$this->_scriptName}\t" . $requestMethod . "\t{$requestParamStr}\t" . str_replace(array("\r", "\n"), array(' ', ' '), trim($msg)) . "\n";


        if (isset($this->_msg [$this->_logFile])) {
            $this->_msg [$this->_logFile] .= $msg;
        } else {
            $this->_msg [$this->_logFile] = $msg;
        }

        return true;
    }

    public function outLog() {
        $dir = DIR_LOG . date('Ym');
        $today = date("md");
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        foreach ($this->_msg as $logFile => $msg) {
            unset($this->_msg[$logFile]);
            $newFile = false;
            $file = "{$dir}/{$today}_{$logFile}.log";
            if (!file_exists($file)) {
                touch($file);
                $newFile = true;
            }
            file_put_contents($file, $msg, FILE_APPEND);
            if ($newFile) {
                chmod($file, 0777);
            }
        }
        return true;
    }

    public function __destruct() {
        $this->outLog();
    }

}
