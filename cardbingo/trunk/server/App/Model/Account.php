<?php

namespace App\Model;

/**
  CREATE TABLE `account` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户账号名',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码  md5({$account}{$salt}{$password})',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '账号当前的状态  0 正常  1 禁止登录。。。。',
  `register_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `sid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '用户所属的服务器组ID',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `idx_account` (`username`) USING BTREE
  ) ENGINE=InnoDB AUTO_INCREMENT=288230376151812761 DEFAULT CHARSET=utf8;
 */



class Account extends Model {

    protected $_name = "account";

    public function login($username, $password) {
        $userAccount = $this->getAccountByUserName($username);
        
        if (!$userAccount || !$userAccount['user_id']) {
            $this->setError(1006, "Account not exist");
            return false;
        }
   
        if ($userAccount['password'] != $this->_password($username, $password)) {
            //dump($userAccount['password']);
            //dump($this->_password($username, $password));
            $this->setError(1007, "Password error");
            return false;
        }
        //如果sid不存在， 表示注册的时候分配失败， 重新分配
        if (!$userAccount['server_id']) {
            $serverid = $this->updateSid($userAccount['user_id']);
            $userAccount['server_id'] = $serverid;
        }
        
        if (!$userAccount['server_id']) {
            //如果没有sid， 则表示服务器组都繁忙， 新用户无法登录， 需要稍后重试
            $this->setError(1008, "server_id error");
            return false;
        }
        
        return $userAccount;
    }

    private function _password($username, $password) {
        $salt = config()['password_salt'];
        return md5("{$username}{$salt}{$password}");
    }
    
    public function bind($userid,$newusername,$newpassword){
        $data = $this->getAccount($userid);
        if ($data["isbind"]){
            $this->setError(1012, "this account is binded");
            return false;
        }
        $oldusername = $data['user_name'];
        $data = [
            'user_id' => $userid,
            'user_name' => $newusername,
            'password' => $this->_password($newusername, $newpassword),
            "register_time" => $data["register_time"],
            "server_id" => $data["server_id"],
            "isbind" => 1,
        ];
        \App\PO\Account::getInstance()->db()->where(["user_id" => $userid])->update($data);
//        wlog($data);
        $this->redis()->multi()
                ->hMset("account:{$userid}", $data)
                ->delete("account:username:{$oldusername}")
                ->set("account:username:{$newusername}", $userid)
                ->exec();
        return true;
    }
    
    public function start($card1,$card2,$card3,$card4,$card5,$card6) {
        $shard = \App\Model\Shard::getInstance();
        $randomBid = $shard->getRandomCard($card1,$card2,$card3,$card4,$card5,$card6);
        
        return true;
    }
    
    public function register($username, $password,$gv,$cid) {
        if ($this->redis()->setnx("account:username:{$username}", 0) === FALSE) {
            //获取账号对应uid是否已经设置， 如果没设置， 代表这用户没注册过, 0值表示有用户正在注册中
            //成功返回true
            $this->setError(1005, "{$username} is registered");
            return false;
        }
        $sid = Shard::getInstance()->getActiveSid();
        //dump("1",$username);
        
        $userid = $username;
        //dump("2",$userid);
        $data = [
            'user_id' => $userid,
            'user_name' => $username,
            'password' => $this->_password($username, $password),
            "register_time" => NOW,
            "server_id" => $sid,
            "isbind" => 0,
            'game_version' => $gv,
            'channel_id' => $cid
        ];
        
        \App\PO\Account::getInstance()->db()->insert($data,FALSE);
        
        //if (!$userid) {
            //数据库出错或者username重复， redis中的a:id:{$account}数据丢失导致,需要额外将丢失的数据找回
            //TODO log
        //    return false;
        //}
        //设置redis
        //unset($data['register_time']);
        $this->redis()->multi()
                ->hMset("account:{$userid}", $data)
                ->set("account:username:{$username}", $userid)
                ->exec();
        return $data;
    }
                                                        
    private function _setRedisCache($uid, $data) {
        //unset($data['user_id']);
        //unset($data['register_time']);
        $cacheKey = "account:{$uid}";
        //设置redis
        $this->redis()->hMSet($cacheKey, $data);
    }

    public function getAccount($uid) {
        $cacheKey = "account:{$uid}";
        $datar = $this->redis()->hGetAll($cacheKey);
        if ($datar) {
            //缓存中没有
            return $datar;
        }
        $datad = \App\PO\Account::getInstance()->db()->where(["user_id" => $uid])->get();
        $this->_setRedisCache($uid, $datad);
        return $datad;
    }

    public function getAccountByUserName($username) {
        $userid = $this->redis()->get("account:username:{$username}");
        //dump("getAccountByUserName",$userid);
        if (!$userid) {
            $data = \App\PO\Account::getInstance()->db()->where(["user_name" => $username])->get();
            if ($data && isset($data['user_id']) && $data['user_id']) {
                $userid = $data['user_id'];
                $this->redis()->set("account:username:{$username}", $userid);
            } else {
                return false;
            }
        }
        $userAccount = $this->getAccount($userid);
        $userAccount['user_id'] = $userid;
        return $userAccount;
    }

    public function updateSid($userid) {
        $serverid = Shard::getInstance()->getActiveSid();
        if (!$serverid) {
            return 0;
        }
        $userAccount['server_id'] = $serverid;
        $this->_setRedisCache($userid, $userAccount);
        \App\PO\Account::getInstance()->db()->updateById($userid,['server_id' => $serverid]);
        return $serverid;
    }
    
    public function giveReward($userid) {
        $reward = Shard::getInstance()->gameConfig('register')['reward'];
        foreach ($reward as $value) {
            Resouce::giveResouce($this,$userid, $value);
        }
    }

}
