<?php

function config($file = 'Config') {
    static $configs = array();
    if (empty($configs[$file])) {
        $configs[$file] = require(DIR . "Config/{$file}.php" );
    }
    return $configs[$file];
}

function tplt($file) {
    static $tplts = array();
    if (empty($tplts[$file])) {
        $tplts[$file] = require(DIR . "Tplt/Templates/{$file}.php" );
    }
    return $tplts[$file];
}

function write_tplt($file,$data){
    $writePath = DIR . "Tplt/Templates/{$file}.php";
    //dump($data);
    $data = var_export($data, true);
    //dump($writePath);
    //dump($data);
    //dump("<?php \r\n return {$data};");
    file_put_contents($writePath,"<?php \r\n return {$data};");
}

function object_array($array) {  
    if(is_object($array)) {  
        $array = (array)$array;  
     } 
    if(is_array($array)) {  
         foreach($array as $key=>$value) {
             $array[$key] = object_array($value);  
         }  
 
     }  
     return $array;  
}

// convert a multidimensional array to url save and encoded string
// usage: string Array2String( array Array )
function Array2String($Array)
{
    $Return='';
    $NullValue="^^^";
    foreach ($Array as $Key => $Value) {
        if(is_array($Value))
            $ReturnValue='^^array^'.Array2String($Value);
        else
            $ReturnValue=(strlen($Value)>0)?$Value:$NullValue;
        $Return.=urlencode(base64_encode($Key)) . '|' . urlencode(base64_encode($ReturnValue)).'||';
    }
    return urlencode(substr($Return,0,-2));
}

// convert a string generated with Array2String() back to the original (multidimensional) array
// usage: array String2Array ( string String)
function String2Array($String)
{
    $Return=array();
    $String=urldecode($String);
    $TempArray=explode('||',$String);
    $NullValue=urlencode(base64_encode("^^^"));
    foreach ($TempArray as $TempValue) {
        list($Key,$Value)=explode('|',$TempValue);
        $DecodedKey=base64_decode(urldecode($Key));
        if($Value!=$NullValue) {
            $ReturnValue=base64_decode(urldecode($Value));
            if(substr($ReturnValue,0,8)=='^^array^')
                $ReturnValue=String2Array(substr($ReturnValue,8));
            $Return[$DecodedKey]=$ReturnValue;
        }
        else
        $Return[$DecodedKey]=NULL;
    }
    return $Return;
}



function get_day_remain_second($now){
    $data1 = date('Y-m-d-H-i-s',$now);
    $data2 = explode("-",$data1);
    $Hour = $data2[3];
    $Minute = $data2[4];
    $Second = $data2[5];
    $start_time = strtotime("{$Hour}:{$Minute}:{$Second}");
    $end_time = strtotime("23:59:59");
    return $end_time-$start_time; //剩余的秒数
}

function get_register_day_time($register){
    $data1 = date('Y-m-d-H-i-s',$register);
    $data2 = explode("-",$data1);
    $Year = $data2[0];
    $Month = $data2[1];
    $Day = $data2[2];
    return strtotime("{$Year}-{$Month}-{$Day}");
}

function dump($args) {
    if (!config()['debug']) {
        return;
    }
    $string = '';
    foreach (func_get_args() as $value) {
        $string .= '<pre>' . htmlentities($value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE))) . "</pre>" . PHP_EOL;
    }
    echo $string;
}

function wlog($msg,$file = ''){
    $log=\Core\Log::getInstance();
    $log->writeLog($msg,$file = '');
}

/**
 * 获取客户端的ip
 *
 * @param boolean $format
 *            - 返回格式 0 : 原格式 1:数字
 * @return string/int
 */
function getIp($format = 0) {
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER ['REMOTE_ADDR'];
    }
    preg_match('/[\d\.]{7,15}/', $ip, $ipmatches);
    $onlineip = $ipmatches [0] ? $ipmatches [0] : '0.0.0.0';

    if ($format) {
        return ip2long($onlineip);
    } else {
        return $onlineip;
    }
}

function checkUid($uid) {
    //uid范围应该在  288230376151711744 < uid <= 576460752303423487   000001 + 58 bit     也就是 $uid >> 58 & 0x3F == 1
    if (!preg_match('/^[1-9]\d*$/', $uid) //&&
    //gmp_cmp($uid, "288230376151711744") < 0 &&
    //gmp_cmp($uid, "576460752303423487" > 0)
    ) {
        return FALSE;
    }
    return TRUE;
}

//class IDWorker extends Model
//{
//    static $workerId;
//    static $twepoch = 1418801787000;
//    static $sequence = 0;
//    static $sequenceMask = 1023;
//    private  static $lastTimestamp = -1;
//
//    function __construct($channelId){
//        //dump("IDWorker channelId",$channelId);
//        if( $channelId < 0 )
//        {
//            throw new Exception("worker Id can't be greater than 15 or less than 0");
//        }
//        self::$workerId=$channelId;
//
//    }
//
//    function timeGen(){
//        return floor(microtime(true) * 1000);
//    }
//    function  tilNextMillis($lastTimestamp) {
//        $timestamp = $this->timeGen();
//        while ($timestamp <= $lastTimestamp) {
//            $timestamp = $this->timeGen();
//        }
//
//        return $timestamp;
//    }
//
//    function  nextId()
//    {
//        $timestamp=$this->timeGen();
//        if(self::$lastTimestamp == $timestamp) {
//            self::$sequence = (self::$sequence + 1) & self::$sequenceMask;
//            if (self::$sequence == 0) {
//                $timestamp = $this->tilNextMillis(self::$lastTimestamp);
//            }
//        } else {
//            self::$sequence  = 0;
//        }
//        if ($timestamp < self::$lastTimestamp) {
//            throw new Excwption("Clock moved backwards.  Refusing to generate id for ".(self::$lastTimestamp-$timestamp)." milliseconds");
//        }
//        self::$lastTimestamp  = $timestamp;
//        $base = decbin(pow(2,40) - 1 + ($timestamp-self::$twepoch));
//        $machineid = decbin(pow(2,9) - 1 + self::$workerId);
//        $nextId = bindec($base.$machineid.self::$sequence);
//        return $nextId;
//    }
//
//}

trait ServiceSingleton {

    public static function getInstance($key = "default") {
        static $_instances = NULL;
        if ($_instances && isset($_instances[$key]) && $_instances[$key] instanceof self) {
            return $_instances[$key];
        }
        $_instances[$key] = self::_serviceInit($key);
        return $_instances[$key];
    }

    public static function _serviceInit($key) {
        $class = __CLASS__;
        return new $class($key);
    }

    public function __clone() {
        trigger_error('Cloning ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
    }

}

/**
 * 根据权重随机出数组的一个元素
 * 
 * @param array $data
 * eg ['key1' => 1, 'key2' =>1]
 * return  'key1';
 */
function randByWeights($randData = []) {
    if (empty($randData) || !is_array($randData)) {
        return FALSE;
    }
    if (count($randData) === 1) {
        return key($randData);
    }
    $sum = array_sum($randData);
    if ($sum <= 0) {
        return FALSE;
    }
    $rand = rand(1, $sum);
    $tmpWeightSum = 0;
    foreach ($randData as $key => $weight) {
        $tmpWeightSum += $weight;
        if ($rand <= $tmpWeightSum) {
            return $key;
        }
    }
    return FALSE;
}
