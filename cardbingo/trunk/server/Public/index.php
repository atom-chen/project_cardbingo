<?php

require dirname(dirname(__FILE__)) . '/Lib/Bootstrap.php';

$dispatch = new \Core\Dispatch();

try {
    $dispatch->run();
} catch (Exception $e) {
    $errmsg = config()['debug'] ? $e->getMessage() : "Server Error";
    echo new \Core\Response($errmsg, $e->getCode());
}
