<?php

namespace App\Controller;

class Account extends Controller {
    
    function start() {
        $cardData = json_decode(trim($this->params['card']));
        $card1Array = $cardData->card1;
        $card2Array = $cardData->card2;
        $card3Array = $cardData->card3;
        $card4Array = $cardData->card4;
        $card5Array = $cardData->card5;
        $card6Array = $cardData->card6;
        
        $shard = \App\Model\Shard::getInstance();
        $mAccount = \App\Model\Account::getInstance();
        if (!$mAccount->start($card1Array,$card2Array,$card3Array,$card4Array,$card5Array,$card6Array)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        $returnData=[];
        return new \Core\Response($returnData);
    }
    function login() {
        $username = trim($this->params['username']);
        $password = trim($this->params['password']);
        $pv = trim($this->params['pv']);
        $rv = trim($this->params['rv']);
        $av = trim($this->params['av']);
        $dv = trim($this->params['dv']);
        $gv = trim($this->params['gv']);
        $cid = trim($this->params['cid']);
        
        $datav = json_decode(trim($this->params['verify']));
        $initdata = json_decode(trim($this->params['initdata']));
 
        $shard = \App\Model\Shard::getInstance();
        if (!$shard->chkVersion($pv,$rv)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        //数据校验
        if (!$shard->chkData($dv,$datav)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        //注册名密码校验
        if (!$shard->chkRegister($username,$password)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        $mAccount = \App\Model\Account::getInstance();
        $userAccount = $mAccount->login($username, $password);
        if (!$userAccount || !$userAccount['user_id']) {
            //登录验证不通过
            return new \Core\Response($mAccount->errMsg(), $mAccount->errCode());
        }
        $userid = $userAccount['user_id'];
        $mAccessToken = \App\Model\AccessToken::getInstance();
        $ret = $mAccessToken->generate($userAccount);
        $newParm = $shard->commonBusinessParmReturn($mAccount,$userid);
        $isbind = $newParm[0];
        $newtoken = $ret['accessToken'];
        $timestamp = $ret['timestamp'];
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
        
        //验证通过
        $shard->commonInit($userid,$initdata,$gv,$cid);
        $mRole->addVersion();
        $returnData = $shard->commonInitReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,[]);
        return new \Core\Response($returnData);
    }
    
    function bind() {
        $oldusername = trim($this->params['old_username']);
        $oldpassword = trim($this->params['old_password']);
        $newusername = trim($this->params['new_username']);
        $newpassword = trim($this->params['new_password']);
        $pv = trim($this->params['pv']);
        $rv = trim($this->params['rv']);
        $av = trim($this->params['av']);
        $dv = trim($this->params['dv']);
        $gv = trim($this->params['gv']);
        $cid = trim($this->params['cid']);
        $datav = json_decode(trim($this->params['verify']));
        $initdata = json_decode(trim($this->params['initdata']));
 
        $shard = \App\Model\Shard::getInstance();
        if (!$shard->chkVersion($pv,$rv)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        //数据校验
        if (!$shard->chkData($dv,$datav)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        //注册名密码校验
        if (!$shard->chkRegister($oldusername,$oldpassword)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        $mAccount = \App\Model\Account::getInstance();
        $userAccount = $mAccount->login($oldusername, $oldpassword);
        if (!$userAccount || !$userAccount['user_id']) {
            //登录验证不通过
            return new \Core\Response($mAccount->errMsg(), $mAccount->errCode());
        }
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
        
        //验证通过
        $shard->commonInit($userid,$initdata,$gv,$cid);
        if (!$mAccount->bind($userid,$newusername,$newpassword)) {
            //绑定验证不通过
            return new \Core\Response($mAccount->errMsg(), $mAccount->errCode());
        }
        $mRole->addVersion();
        $mAccount->giveReward($userid);
        $account = $mAccount->getAccount($userid);
        $isbind = intval($account['isbind']);
        $returnData = $shard->commonInitReturn($newtoken,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,[]);
        return new \Core\Response($returnData);
    }
    

    function register() {
        $username = trim($this->params['username']);
        $password = trim($this->params['password']);
        $pv = trim($this->params['pv']);
        $rv = trim($this->params['rv']);
        $dv = trim($this->params['dv']);
        $gv = trim($this->params['gv']);
        $cid = trim($this->params['cid']);
        $datav = json_decode(trim($this->params['verify']));
//        $tourist = intval(trim($this->params['tourist']));
        //dump($datav->coin);
        //dump($datav->diamond);
        //$sid = 0;//为0时自动分配
        //if (config()['debug']) {
        //    $sid = intval($this->params['sid']);
        //}
        $shard = \App\Model\Shard::getInstance();
        $shard->set_channelid($cid);
        
        if (!$shard->chkVersion($pv,$rv)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        
        //初始数据校验
        if (!$shard->chkInitData($dv,$datav)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }
        //注册名密码校验
        if (!$shard->chkRegister($username,$password)){
            return new \Core\Response($shard->errMsg(), $shard->errCode());
        }

        $mAccount = \App\Model\Account::getInstance();
        
        //注册
        $userAccount = $mAccount->register($username, $password,$gv,$cid);
        if (!$userAccount || !$userAccount['user_id']) {
            return new \Core\Response($mAccount->errMsg(), $mAccount->errCode());
        }
        
        $mRole = \App\Model\Role::getInstance($userAccount['user_id']);
//        $roleData = $mRole->create();
        $roleData = $mRole->create($userAccount["register_time"],$gv,$cid);
        
        if (!$roleData || !$roleData['role_id']) {
            return new \Core\Response($mRole->errMsg(), $mRole->errCode());
        }
        
        //$mHero = \App\Model\Hero::getInstance($userAccount['user_id']);
        //$heroData = $mHero->create();
       
//        if (!$heroData) {
//            return new \Core\Response($mHero->errMsg(), $mHero->errCode());
//        }
        
//        if (!$tourist){
//            $mAccount->giveReward($userAccount['user_id']);
//        }
        
        $returnData = ['av'=>$roleData['archive_version']];
        return new \Core\Response($returnData);
    }

}
