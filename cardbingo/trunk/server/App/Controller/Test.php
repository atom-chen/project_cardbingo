<?php

namespace App\Controller;

class Test extends \Core\Controller\Response{

    
    public function index(){
        $nowTime = NOW;
        $register = 1463554800;
        $registers = mktime(15,0,0,5,20,2016);
        $totalday = ceil(($nowTime - $registers)/Day);
        echo $totalday;
        $day = $totalday%30;
        echo $day;

        $shard = \App\Model\Shard::getInstance();
        $shard->test();
          
        return new \Core\Response(Null,0);
    }
    
}
