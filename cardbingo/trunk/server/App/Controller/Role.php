<?php

namespace App\Controller;

class Role extends Controller {
    
    function sync_data() {
        $av = trim($this->params['av']);
        $username = trim($this->params['username']);
        $password = trim($this->params['password']);
        $pv = trim($this->params['pv']);
        $rv = trim($this->params['rv']);
        $dv = trim($this->params['dv']);
        $gv = trim($this->params['gv']);
        $cid = trim($this->params['cid']);
        $datav = json_decode(trim($this->params['verify']));
        $initdata = json_decode(trim($this->params['initdata']));
        $token = trim($this->params['token']);
        
        $shard = \App\Model\Shard::getInstance();
        
        if (!$shard->generelChk($pv, $rv, $token, $username, $password, $dv, $datav, $initdata)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        $mAccount = \App\Model\Account::getInstance();
        $userAccount = $mAccount->getAccountByUserName($username);
        
        $userid = $userAccount['user_id'];
        
        $newParm = $shard->commonBusinessParmReturn($mAccount,$userid);
        $isbind = $newParm[0];
        $newtoken = $newParm[1];
        $timestamp = $newParm[2];
        $mRole = $newParm[3];
        $mItem = $newParm[4];
        $mHero = $newParm[5];
        $mCopy = $newParm[6];
        $mTask = $newParm[7];
        $mGiftMall = $newParm[8];
        $mItemMall = $newParm[9];
        
        if(!$shard->chkAV($userid,$av)){
            $returnData = $shard->commonInitAvReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,$mTask,$mGiftMall,$mItemMall);
            return new \Core\Response($returnData,$shard->errCode());
        }
        
        $shard->commonInit($userid,$initdata,$gv,$cid);
        $mRole->addVersion();
        $returnData = $shard->commonInitReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,[]);
        return new \Core\Response($returnData);
    }
    
    function sync_mall() {
        $av = trim($this->params['av']);
        $username = trim($this->params['username']);
        $password = trim($this->params['password']);
        $pv = trim($this->params['pv']);
        $rv = trim($this->params['rv']);
        $dv = trim($this->params['dv']);
        $gv = trim($this->params['gv']);
        $cid = trim($this->params['cid']);
        $datav = json_decode(trim($this->params['verify']));
        $initdata = json_decode(trim($this->params['initdata']));
        $token = trim($this->params['token']);
        
        $shard = \App\Model\Shard::getInstance();
        
        if (!$shard->generelChk($pv, $rv, $token, $username, $password, $dv, $datav, $initdata)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        $mAccount = \App\Model\Account::getInstance();
        $userAccount = $mAccount->getAccountByUserName($username);
        
        $userid = $userAccount['user_id'];
        
        $newParm = $shard->commonBusinessParmReturn($mAccount,$userid);
        $isbind = $newParm[0];
        $newtoken = $newParm[1];
        $timestamp = $newParm[2];
        $mRole = $newParm[3];
        $mItem = $newParm[4];
        $mHero = $newParm[5];
        $mCopy = $newParm[6];
        $mTask = $newParm[7];
        $mGiftMall = $newParm[8];
        $mItemMall = $newParm[9];
        
        if(!$shard->chkAV($userid,$av)){
            $returnData = $shard->commonInitAvReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,$mTask,$mGiftMall,$mItemMall);
            return new \Core\Response($returnData,$shard->errCode());
        }
        
        $shard->commonInit($userid,$initdata,$gv,$cid);
        
        $mall = $shard->get_tplt("mall_tplt");
        $mallList = array();
        foreach ($mall as $key => $value) {
            foreach ($value as $keys => $values) {
                if($keys=="channel" && intval($values)==$cid){
                    $mallList[$key] = $value;
                }
            }       
        }
         
        //$shard->transTplt($mall);
        $malltplt = ["mall_tplt"=>$mallList];
        //$malltplt = ["mall_tplt"=>urldecode(json_encode($shard->transTplt($mall)))];
        $mRole->addVersion();
        $returnData = $shard->commonInitReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,$malltplt);
        return new \Core\Response($returnData);
    }
    
    
    
    
    
    
    
   

}
