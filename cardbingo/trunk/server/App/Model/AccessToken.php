<?php

namespace App\Model;

class AccessToken extends Model {

    private $_expire = 86400;
    protected $_name = "accessToken";

    public function generate($userAccount) {
        $params = [];
        $params['server_id'] = $userAccount['server_id'];
        $params['timestamp'] = NOW;
        $params['user_id'] = $userAccount['user_id'];
        $params['accessToken'] = \Core\Sign::generate($params);
        $this->_setCache($params['user_id'], $params);
        return $params;
    }
    
    public function get($userid) {
        $cacheKey = "account:token:{$userid}";
        $params = $this->redis()->hGetAll($cacheKey);
        //dump($userid);
        if (!$params || !$params['accessToken']) {
            $this->setError(1009, "Token is expired");
            return false;
        }
        
        return $params;
    }

    public function check($username,$password,$accessToken) {
        $mAccount = \App\Model\Account::getInstance();
        $userAccount = $mAccount->getAccountByUserName($username);
        $userid = $userAccount['user_id'];
        if (!$userAccount || !$userid) {
            //登录验证不通过
            $this->setError(1006, "Account not exist");
            return false;
        }
        $cacheKey = "account:token:{$userid}";
        $params = $this->redis()->hGetAll($cacheKey);
        $token = $accessToken;
        //dump("check");
        //dump($userid);
        if (!$params || !$token) {
            $mAccount = \App\Model\Account::getInstance();
            $userAccount = $mAccount->login($username, $password);
            if (!$userAccount || !$userAccount['user_id']) {
                //登录验证不通过
                $this->setError(1006, "Account not exist");
                return false;
            }
            $params=$this->generate($userAccount);
            $token = $params['accessToken'];
        }

        //$params['user_id'] = $uid;
        if (!\Core\Sign::check($token, $params)){
            $this->setError(1010, "Token is no existed");
            return false;
        }
        
        return true;
    }

    private function _setCache($uid, $params) {
        $cacheKey = "account:token:{$uid}";
        //unset($params['user_id']);
        //unset($params['timestamp']);
        //dump($uid);
        //dump($params);
        $this->redis()->multi()
                ->hMset($cacheKey, $params)
                ->expire($cacheKey, $this->_expire)
                ->exec();
    }

    public function delete($uid) {
        //目前可删可不删
        $cacheKey = "account:token:{$uid}";
        return $this->redis()->del($cacheKey);
    }

}
