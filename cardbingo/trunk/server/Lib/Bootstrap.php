<?php

// System Start Time
//define('START_TIME', microtime(true));
// System Start Memory
//define('START_MEMORY_USAGE', memory_get_usage());
define('DS', DIRECTORY_SEPARATOR);
define('DIR', dirname(realpath(__DIR__)) . '/');
define('DIR_LIB', DIR . "Lib/");
define('DIR_CORE', DIR . "Lib/Core/");
define('DIR_APP', DIR . "App/");
define('DIR_VENDOR', DIR . "App/Vendor/");
define('DIR_VO', DIR . "App/VO/");
define('DIR_VIEW', DIR . "View/");
define('DIR_TMP', DIR . "Tmp/");
define('DIR_LOG', DIR . "Tmp/Log/");
define('DIR_CACHE', DIR . "Tmp/Cache/");
define('DIR_TPLT', DIR . "Tplt/");
require(DIR_LIB . 'Common.php');
//autoload
require(DIR_CORE . 'Autoloader.php');
\Core\Autoloader::register(config()['namespaces']);
//Tplt\TpltReader::readTplt();
date_default_timezone_set(config()["timezone"]);
if (!config()['debug']) {
    // 关闭错误提示
    error_reporting(0);
    ini_set("display_errors", 'Off');
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}

define('NOW', time());
define('Day', 86400);