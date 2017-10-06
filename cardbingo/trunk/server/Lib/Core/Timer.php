<?php

namespace Core;

/**
 * 断点时间间隔记录
 */
class Timer {
    use \ServiceSingleton;
    private $_logs = array();
    private $_first = 0;
    private $_last = 0;
    private $_debug = true;
    private $_file = 'timer.log';
    private $_lower = 0;
    private $_startTime = '';
    private $_alertTime = 0;
    private $_header = '';

    const LOG_LIMIT_COUNT = 200;

    /**
     * 断点记录
     *
     * @param string $file            
     * @param int $lower
     *            最低的开始记录时间 单位毫秒
     */
    public function __construct($file = '', $alertTime = 1000, $lower = 0) {
//        $this->_debug = clsCommon::getConfig ( "config", "debug" );
        if ($file) {
            $this->_file = $file;
        }

        if (!$this->_debug) {
            return;
        }
        $this->_lower = $lower;
        $this->_alertTime = $alertTime;
        $this->_start();
    }
    
    public function setAlertTime($alertTime){
        $this->_alertTime = $alertTime;
    }
    
    public function setLower($lower){
        $this->_lower = $lower;
    }
    
    public function __destruct() {
        $this->out(true);
    }

    private function _start() {
        $_timer = $this->_timer();
        $this->_startTime = date('Y-m-d H:i:s');
        $this->_first = $_timer;
        $this->_last = $_timer;
    }

    private function _timer() {
        $t = explode(" ", microtime());
        $s = substr($t [1], - 3);
        return ((float) $t [0] + (float) $s) * 1000;
    }

    public function add($msg = '') {
        if (!$this->_debug)
            return;
        $nt = $this->_timer();
        $diff = $nt - $this->_last;
        $this->_last = $nt;
        $this->_logs [] = array(
            $diff,
            $msg
        );

        if (count($this->_logs) >= self::LOG_LIMIT_COUNT) {
            $this->out();
            $this->_logs = array();
        }
    }

    public function out($calTotal = false) {
        if (!$this->_debug)
            return;
        $cost = $this->_timer() - $this->_first;
        if ($cost < $this->_lower)
            return;

        //第一次的时候写入头信息
        if (!$this->_header) {
            $file = $_SERVER ['REQUEST_URI'] ? $_SERVER ['REQUEST_URI'] : ($_SERVER ['PHP_SELF'] ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME']);
            $this->_header = $msg = "[" . $this->_startTime . "] {$file} \n";
            if ($_REQUEST) {
                $msg .= "[REQUEST DATA] \n" . var_export($_REQUEST, true) . "\n\n";
            }
        }

        foreach ($this->_logs as $v) {
            $msg .= "\t" . sprintf("%0.2f", $v [0]) . " ms\t" . $v [1] . "\n";
        }

        //结束时才写入总时间
        if ($calTotal) {
            $total = "\t**All cost " . sprintf("%0.2f", $cost) . " ms\n\n";
            $msg .= $total;
            $alertMsg = $this->_header . $total;
        }

        $dir = DIR_LOG . date('Ym');
        $today = date("md");
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = "{$dir}/{$today}_{$this->_file}.timer.log";
        file_put_contents($file, $msg, FILE_APPEND);

        if ($calTotal && $this->_alertTime && $cost >= $this->_alertTime) {
            $alertFile = "{$dir}/{$today}_{$this->_file}_alert.timer.log";
            $this->_alert($alertFile, $alertMsg);
        }
    }

    private function _alert($file, $alertMsg) {
        file_put_contents($file, $alertMsg, FILE_APPEND);
    }

}
