<?php

namespace App\Controller;


class Server extends Controller {

    public function redis_reset(){
        $shard = \App\Model\Shard::getInstance();
        $shard->redis_reset();
        return new \Core\Response("reset is ok");
    }
    
    public function doMysql(){
        $shard = \App\Model\Shard::getInstance();
        return new \Core\Response($shard->doMysql());
    }
    
    public function get_reward_index_tplt(){
        $version = intval(trim($this->params['Version']));
        $shard = \App\Model\Shard::getInstance();
        return new \Core\Response($shard->get_reward_index_tplt($version));
    }

    
}
