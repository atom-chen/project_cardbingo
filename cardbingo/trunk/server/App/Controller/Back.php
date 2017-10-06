<?php

namespace App\Controller;

class Back extends Controller {
    
    public function recharge(){
        $roleid = intval(trim($this->params['RoleID']));
        $orderid = intval(trim($this->params['OrderID']));
        $money = intval(trim($this->params['Money']));
        $rechargeid = intval(trim($this->params['RechargeID']));
        $shard = \App\Model\Shard::getInstance();
        //return new \Core\Response($shard->get_reward_index_tplt($version));
    }
    
    public function broadcast(){
        $content = trim($this->params['Content']);
//        {"title":"test","priority":"1","Content":"test"}
//        wlog($content);
        $shard = \App\Model\Shard::getInstance();
        $shard->save_broadcast($content);
        return new \Core\Response(Null,0);
        //return new \Core\Response($shard->get_reward_index_tplt($version));
    }
    
    public function get_broadcast() {
        $shard = \App\Model\Shard::getInstance();
        $content = $shard->get_broadcast();
        return new \Core\Response(["broadcast" =>$content]);
        
    }
    
    
}
